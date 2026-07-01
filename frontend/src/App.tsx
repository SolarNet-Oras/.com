import React from 'react';
import './index.css';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from '@/context/AuthContext';
import { ThemeProvider } from '@/context/ThemeContext';
import { ProtectedRoute } from '@/components/ProtectedRoute';
import LoginPage from '@/pages/LoginPage';
import NewDashboardPage from '@/pages/NewDashboardPage';
import CustomersPage from '@/pages/CustomersPage';
import CreateCustomerPage from '@/pages/CreateCustomerPage';

// ============================================================================
// Main App Component
// ============================================================================

const App: React.FC = (): JSX.Element => {
  return (
    <ThemeProvider>
      <Router>
        <AuthProvider>
          <div className="App min-h-screen bg-background">
            <Routes>
              {/* Public Routes */}
              <Route path="/login" element={<LoginPage />} />
              
              {/* Protected Routes */}
              <Route
                path="/dashboard"
                element={
                  <ProtectedRoute>
                    <NewDashboardPage />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/customers"
                element={
                  <ProtectedRoute>
                    <CustomersPage />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/customers/create"
                element={
                  <ProtectedRoute>
                    <CreateCustomerPage />
                  </ProtectedRoute>
                }
              />
              
              {/* Default Route */}
              <Route path="/" element={<Navigate to="/dashboard" replace />} />
              
              {/* Catch all - redirect to dashboard */}
              <Route path="*" element={<Navigate to="/dashboard" replace />} />
            </Routes>
          </div>
        </AuthProvider>
      </Router>
    </ThemeProvider>
  );
};

export default App;
