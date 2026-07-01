<?php

namespace App\Services;

use App\Models\Router;

class MikrotikScriptGenerator
{
    /**
     * Generate RouterOS setup script for API configuration
     * 
     * @param Router $router
     * @param string $billingSystemIp (optional - IP of billing system for firewall rules)
     * @return string
     */
    public function generateSetupScript(Router $router, ?string $billingSystemIp = null): string
    {
        $username = $router->username;
        $password = $router->password;
        $apiPort = $router->port;
        
        $script = <<<SCRIPT
# ============================================================
# MikroTik RouterOS API Setup Script
# Generated for: {$router->name}
# Router: {$router->host}
# Generated: {{date}}
# ============================================================
#
# INSTRUCTIONS:
# 1. Connect to your MikroTik router via Winbox or SSH
# 2. Open "New Terminal" window
# 3. Copy and paste this entire script
# 4. Press Enter to execute
#
# ============================================================

:log info "Starting ISP Billing System API Setup..."

# Step 1: Create API user for billing system
/user group
:if ([:len [find name="billing_api_group"]] = 0) do={
    add name="billing_api_group" \\
        policy=api,read,write,policy,test,password,web,!local,!telnet,!ssh,!ftp,!reboot,!sensitive
    :log info "Created user group: billing_api_group"
} else={
    :log info "User group 'billing_api_group' already exists"
}

/user
:if ([:len [find name="{$username}"]] = 0) do={
    add name="{$username}" \\
        password="{$password}" \\
        group=billing_api_group \\
        comment="ISP Billing System API Access"
    :log info "Created API user: {$username}"
} else={
    set [find name="{$username}"] password="{$password}" group=billing_api_group
    :log info "Updated existing user: {$username}"
}

# Step 2: Enable API service on port {$apiPort}
/ip service
set api address="" port={$apiPort} disabled=no
:log info "Enabled API service on port {$apiPort}"

SCRIPT;

        // Add firewall rules if billing system IP is provided
        if ($billingSystemIp) {
            $script .= <<<FIREWALL

# Step 3: Configure firewall to allow API access from billing system
/ip firewall filter
:if ([:len [find comment="Allow Billing System API"]] = 0) do={
    add chain=input \\
        protocol=tcp \\
        dst-port={$apiPort} \\
        src-address={$billingSystemIp} \\
        action=accept \\
        comment="Allow Billing System API" \\
        place-before=0
    :log info "Added firewall rule to allow API from {$billingSystemIp}"
} else={
    :log info "Firewall rule for billing API already exists"
}

FIREWALL;
        }

        $script .= <<<SETUP

# Step 4: Create address list for suspended customers (optional)
/ip firewall address-list
:if ([:len [find list="suspended_customers"]] = 0) do={
    :log info "Created address list: suspended_customers"
} else={
    :log info "Address list 'suspended_customers' exists"
}

# Step 5: Test API connectivity
:log info "Testing API service status..."
/ip service print where name=api

# Setup Complete!
:log info "=== ISP Billing System API Setup Complete ==="
:log info "API User: {$username}"
:log info "API Port: {$apiPort}"
:log info ""
:log info "Next steps:"
:log info "1. Test connection from billing system"
:log info "2. Click 'Test Connection' button in billing dashboard"
:log info ""
:log info "If connection fails, check:"
:log info "- Firewall rules (IP > Firewall > Filter Rules)"
:log info "- API service enabled (IP > Services)"
:log info "- User permissions (System > Users)"

SETUP;

        return str_replace('{{date}}', now()->format('Y-m-d H:i:s'), $script);
    }

    /**
     * Generate queue management script template
     * 
     * @return string
     */
    public function generateQueueManagementScript(): string
    {
        return <<<SCRIPT
# ============================================================
# Queue Management Script Template
# ============================================================
# This script will be auto-executed by the billing system
# when managing customer bandwidth.
#
# The billing system will:
# - Create simple queues for each active customer
# - Update queues when service plan changes
# - Remove/throttle queues on suspension
#
# Queue naming convention: customer-{customer_id}
# Example: customer-019f1dec-51d9-7358-b051-800af53de299
#
# ============================================================

# Example: Add a customer queue
/queue simple add \\
    name="customer-XXXXX" \\
    target=192.168.1.100/32 \\
    max-limit=100M/50M \\
    burst-limit=150M/75M \\
    burst-threshold=75M/37.5M \\
    burst-time=16s/16s \\
    priority=8/8 \\
    comment="Customer: John Doe - Plan: Gold 100Mbps"

# Example: Update queue
/queue simple set [find name="customer-XXXXX"] max-limit=200M/100M

# Example: Remove queue
/queue simple remove [find name="customer-XXXXX"]

# Example: Throttle suspended customer to 64kbps
/queue simple set [find name="customer-XXXXX"] max-limit=64k/64k

SCRIPT;
    }

    /**
     * Generate firewall redirect script for payment portal
     * 
     * @param string $paymentPortalUrl
     * @return string
     */
    public function generatePaymentRedirectScript(string $paymentPortalUrl): string
    {
        return <<<SCRIPT
# ============================================================
# Payment Portal Redirect Script
# ============================================================
# Redirects suspended customers to payment portal
#
# Usage: Run this after setting up the billing system
# ============================================================

# Create walled garden for payment portal domain
/ip hotspot walled-garden
add dst-host={$paymentPortalUrl} comment="Allow access to payment portal"

# Create NAT rule to redirect HTTP traffic to payment portal
/ip firewall nat
add chain=dstnat \\
    protocol=tcp \\
    dst-port=80 \\
    src-address-list=suspended_customers \\
    action=redirect \\
    to-ports=80 \\
    comment="Redirect suspended customers to payment portal"

# Create NAT rule to redirect HTTPS traffic
add chain=dstnat \\
    protocol=tcp \\
    dst-port=443 \\
    src-address-list=suspended_customers \\
    action=redirect \\
    to-ports=443 \\
    comment="Redirect suspended HTTPS to payment portal"

:log info "Payment portal redirect configured for: {$paymentPortalUrl}"

SCRIPT;
    }
}
