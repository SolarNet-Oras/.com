<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send invoice generation notification
     */
    public function sendInvoiceNotification(Invoice $invoice): void
    {
        $customer = $invoice->customer;
        
        // Log notification (in production, integrate with email/SMS service)
        Log::info('Invoice notification', [
            'invoice_number' => $invoice->invoice_number,
            'customer_email' => $customer->email,
            'amount' => $invoice->total,
            'due_date' => $invoice->due_date->format('Y-m-d'),
        ]);

        // TODO: Implement actual email/SMS sending
        // Example: Mail::to($customer->email)->send(new InvoiceGenerated($invoice));
    }

    /**
     * Send payment reminder
     */
    public function sendPaymentReminder(Invoice $invoice): void
    {
        $customer = $invoice->customer;
        $daysUntilDue = now()->diffInDays($invoice->due_date, false);
        
        Log::info('Payment reminder', [
            'invoice_number' => $invoice->invoice_number,
            'customer_email' => $customer->email,
            'days_until_due' => $daysUntilDue,
            'balance' => $invoice->balance,
        ]);

        // TODO: Send actual notification
    }

    /**
     * Send overdue notice
     */
    public function sendOverdueNotice(Invoice $invoice): void
    {
        $customer = $invoice->customer;
        $daysOverdue = now()->diffInDays($invoice->due_date);
        
        Log::info('Overdue notice', [
            'invoice_number' => $invoice->invoice_number,
            'customer_email' => $customer->email,
            'days_overdue' => $daysOverdue,
            'balance' => $invoice->balance,
        ]);

        // TODO: Send actual notification
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation($payment): void
    {
        $customer = $payment->customer;
        
        Log::info('Payment confirmation', [
            'payment_number' => $payment->payment_number,
            'customer_email' => $customer->email,
            'amount' => $payment->amount,
            'method' => $payment->payment_method,
        ]);

        // TODO: Send actual notification
    }

    /**
     * Send service suspension warning
     */
    public function sendSuspensionWarning(Customer $customer): void
    {
        Log::info('Suspension warning', [
            'customer_email' => $customer->email,
            'account_number' => $customer->account_number,
        ]);

        // TODO: Send actual notification
    }

    /**
     * Send ticket update notification
     */
    public function sendTicketUpdate($ticket, string $message): void
    {
        $customer = $ticket->customer;
        
        Log::info('Ticket update', [
            'ticket_number' => $ticket->ticket_number,
            'customer_email' => $customer->email,
            'message' => $message,
        ]);

        // TODO: Send actual notification
    }
}
