#!/usr/bin/env python3
"""
Backend API Testing for ISP Billing System
Tests all API endpoints with proper authentication
"""

import requests
import sys
from datetime import datetime
from typing import Dict, Any, Optional, Tuple

class ISPBillingAPITester:
    def __init__(self, base_url: str = "https://network-ops-center-2.preview.emergentagent.com/api/v1"):
        self.base_url = base_url
        self.token: Optional[str] = None
        self.tests_run = 0
        self.tests_passed = 0
        self.tests_failed = 0
        self.failed_tests = []

    def run_test(self, name: str, method: str, endpoint: str, expected_status: int, 
                 data: Optional[Dict[str, Any]] = None, 
                 auth_required: bool = False) -> Tuple[bool, Dict[str, Any]]:
        """Run a single API test"""
        url = f"{self.base_url}/{endpoint}"
        headers = {'Content-Type': 'application/json'}
        
        if auth_required and self.token:
            headers['Authorization'] = f'Bearer {self.token}'

        self.tests_run += 1
        print(f"\n🔍 Test {self.tests_run}: {name}")
        print(f"   URL: {method} {url}")
        
        try:
            if method == 'GET':
                response = requests.get(url, headers=headers, timeout=10)
            elif method == 'POST':
                response = requests.post(url, json=data, headers=headers, timeout=10)
            elif method == 'PUT':
                response = requests.put(url, json=data, headers=headers, timeout=10)
            elif method == 'DELETE':
                response = requests.delete(url, headers=headers, timeout=10)
            else:
                raise ValueError(f"Unsupported method: {method}")

            success = response.status_code == expected_status
            
            if success:
                self.tests_passed += 1
                print(f"   ✅ PASSED - Status: {response.status_code}")
            else:
                self.tests_failed += 1
                self.failed_tests.append({
                    'name': name,
                    'expected': expected_status,
                    'actual': response.status_code,
                    'response': response.text[:200]
                })
                print(f"   ❌ FAILED - Expected {expected_status}, got {response.status_code}")
                print(f"   Response: {response.text[:200]}")

            try:
                return success, response.json()
            except:
                return success, {'raw': response.text}

        except requests.exceptions.Timeout:
            self.tests_failed += 1
            self.failed_tests.append({'name': name, 'error': 'Request timeout'})
            print(f"   ❌ FAILED - Request timeout")
            return False, {}
        except Exception as e:
            self.tests_failed += 1
            self.failed_tests.append({'name': name, 'error': str(e)})
            print(f"   ❌ FAILED - Error: {str(e)}")
            return False, {}

    def test_health_check(self) -> bool:
        """Test API health endpoint"""
        print("\n" + "="*70)
        print("HEALTH CHECK")
        print("="*70)
        success, _ = self.run_test(
            "API Health Check",
            "GET",
            "../health",
            200
        )
        return success

    def test_v1_status(self) -> bool:
        """Test V1 API status"""
        success, _ = self.run_test(
            "V1 API Status",
            "GET",
            "status",
            200
        )
        return success

    def test_authentication(self) -> bool:
        """Test authentication endpoints"""
        print("\n" + "="*70)
        print("AUTHENTICATION TESTS")
        print("="*70)
        
        # Test login with admin credentials
        success, response = self.run_test(
            "Login with Admin Credentials",
            "POST",
            "auth/login",
            200,
            data={
                "email": "admin@ispbilling.local",
                "password": "password"
            }
        )
        
        if success and response.get('data', {}).get('access_token'):
            self.token = response['data']['access_token']
            print(f"   🔑 Token acquired: {self.token[:20]}...")
            
            # Test /me endpoint
            self.run_test(
                "Get Current User (/me)",
                "GET",
                "auth/me",
                200,
                auth_required=True
            )
            
            return True
        else:
            print("   ⚠️  Failed to acquire token - subsequent tests may fail")
            return False

    def test_login_invalid_credentials(self) -> bool:
        """Test login with invalid credentials"""
        success, _ = self.run_test(
            "Login with Invalid Credentials (should fail)",
            "POST",
            "auth/login",
            401,
            data={
                "email": "invalid@test.com",
                "password": "wrongpassword"
            }
        )
        return success

    def test_dashboard_endpoints(self) -> bool:
        """Test dashboard endpoints"""
        print("\n" + "="*70)
        print("DASHBOARD TESTS")
        print("="*70)
        
        if not self.token:
            print("   ⚠️  Skipping - No authentication token")
            return False
        
        # Test metrics endpoint
        success1, response = self.run_test(
            "Get Dashboard Metrics",
            "GET",
            "dashboard/metrics",
            200,
            auth_required=True
        )
        
        if success1:
            metrics = response.get('data', {})
            print(f"   📊 Metrics received:")
            print(f"      - Total Users: {metrics.get('total_users', 0)}")
            print(f"      - Active Users: {metrics.get('active_users', 0)}")
            print(f"      - Total Subscribers: {metrics.get('total_subscribers', 0)}")
        
        # Test quick stats endpoint
        success2, _ = self.run_test(
            "Get Dashboard Quick Stats",
            "GET",
            "dashboard/quick-stats",
            200,
            auth_required=True
        )
        
        return success1 and success2

    def test_customer_endpoints(self) -> bool:
        """Test customer CRUD endpoints"""
        print("\n" + "="*70)
        print("CUSTOMER CRUD TESTS")
        print("="*70)
        
        if not self.token:
            print("   ⚠️  Skipping - No authentication token")
            return False
        
        # Test list customers
        success1, response = self.run_test(
            "List Customers",
            "GET",
            "customers",
            200,
            auth_required=True
        )
        
        if success1:
            customers = response.get('data', [])
            print(f"   📋 Found {len(customers)} customers")
        
        # Test create customer
        test_account = f"TEST{datetime.now().strftime('%Y%m%d%H%M%S')}"
        success2, create_response = self.run_test(
            "Create New Customer",
            "POST",
            "customers",
            201,
            data={
                "account_number": test_account,
                "full_name": "Test Customer",
                "address": "123 Test Street, Test City",
                "contact_number": "+639123456789",
                "email": "test@example.com",
                "installation_date": datetime.now().strftime('%Y-%m-%d'),
                "monthly_fee": 1500.00,
                "status": "pending"
            },
            auth_required=True
        )
        
        customer_id = None
        if success2:
            customer_id = create_response.get('data', {}).get('id')
            print(f"   ✨ Customer created with ID: {customer_id}")
        
        # Test get single customer
        success3 = True
        if customer_id:
            success3, _ = self.run_test(
                f"Get Customer by ID ({customer_id})",
                "GET",
                f"customers/{customer_id}",
                200,
                auth_required=True
            )
        
        # Test update customer
        success4 = True
        if customer_id:
            success4, _ = self.run_test(
                f"Update Customer ({customer_id})",
                "PUT",
                f"customers/{customer_id}",
                200,
                data={
                    "full_name": "Test Customer Updated",
                    "status": "active"
                },
                auth_required=True
            )
        
        # Test customer statistics
        success5, stats_response = self.run_test(
            "Get Customer Statistics",
            "GET",
            "customers-statistics",
            200,
            auth_required=True
        )
        
        if success5:
            stats = stats_response.get('data', {})
            print(f"   📊 Customer Statistics:")
            print(f"      - Total: {stats.get('total', 0)}")
            print(f"      - Active: {stats.get('active', 0)}")
            print(f"      - Suspended: {stats.get('suspended', 0)}")
            print(f"      - Expired: {stats.get('expired', 0)}")
        
        # Test delete customer (cleanup)
        success6 = True
        if customer_id:
            success6, _ = self.run_test(
                f"Delete Customer ({customer_id})",
                "DELETE",
                f"customers/{customer_id}",
                200,
                auth_required=True
            )
        
        return all([success1, success2, success3, success4, success5, success6])

    def test_customer_search_filter(self) -> bool:
        """Test customer search and filter functionality"""
        print("\n" + "="*70)
        print("CUSTOMER SEARCH & FILTER TESTS")
        print("="*70)
        
        if not self.token:
            print("   ⚠️  Skipping - No authentication token")
            return False
        
        # Test search
        success1, _ = self.run_test(
            "Search Customers",
            "GET",
            "customers?search=test",
            200,
            auth_required=True
        )
        
        # Test status filter
        success2, _ = self.run_test(
            "Filter Customers by Status (active)",
            "GET",
            "customers?status=active",
            200,
            auth_required=True
        )
        
        # Test pagination
        success3, _ = self.run_test(
            "Paginate Customers (per_page=5)",
            "GET",
            "customers?per_page=5",
            200,
            auth_required=True
        )
        
        return all([success1, success2, success3])

    def test_logout(self) -> bool:
        """Test logout endpoint"""
        print("\n" + "="*70)
        print("LOGOUT TEST")
        print("="*70)
        
        if not self.token:
            print("   ⚠️  Skipping - No authentication token")
            return False
        
        success, _ = self.run_test(
            "Logout",
            "POST",
            "auth/logout",
            200,
            auth_required=True
        )
        
        if success:
            self.token = None
            print("   🔓 Logged out successfully")
        
        return success

    def print_summary(self):
        """Print test summary"""
        print("\n" + "="*70)
        print("TEST SUMMARY")
        print("="*70)
        print(f"Total Tests: {self.tests_run}")
        print(f"✅ Passed: {self.tests_passed}")
        print(f"❌ Failed: {self.tests_failed}")
        print(f"Success Rate: {(self.tests_passed/self.tests_run*100):.1f}%")
        
        if self.failed_tests:
            print("\n" + "="*70)
            print("FAILED TESTS DETAILS")
            print("="*70)
            for i, test in enumerate(self.failed_tests, 1):
                print(f"\n{i}. {test.get('name', 'Unknown')}")
                if 'expected' in test:
                    print(f"   Expected: {test['expected']}, Got: {test['actual']}")
                    print(f"   Response: {test.get('response', 'N/A')}")
                if 'error' in test:
                    print(f"   Error: {test['error']}")
        
        print("\n" + "="*70)

def main():
    """Main test execution"""
    print("="*70)
    print("ISP BILLING SYSTEM - BACKEND API TESTING")
    print("="*70)
    print(f"Started at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    
    tester = ISPBillingAPITester()
    
    # Run all tests
    tester.test_health_check()
    tester.test_v1_status()
    tester.test_authentication()
    tester.test_login_invalid_credentials()
    tester.test_dashboard_endpoints()
    tester.test_customer_endpoints()
    tester.test_customer_search_filter()
    tester.test_logout()
    
    # Print summary
    tester.print_summary()
    
    # Return exit code
    return 0 if tester.tests_failed == 0 else 1

if __name__ == "__main__":
    sys.exit(main())
