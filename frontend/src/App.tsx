import './index.css'
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'

function App() {
  return (
    <Router>
      <div className="min-h-screen bg-background">
        <Routes>
          <Route path="/" element={<HomePage />} />
        </Routes>
      </div>
    </Router>
  )
}

function HomePage() {
  return (
    <div className="flex items-center justify-center min-h-screen">
      <div className="text-center space-y-6 p-8">
        <h1 className="text-4xl font-bold text-foreground">
          ISP Billing & Network Management System
        </h1>
        <p className="text-xl text-muted-foreground">
          MikroTik IPoE Management Platform
        </p>
        <div className="flex gap-4 justify-center mt-8">
          <div className="bg-card border border-border rounded-lg p-6 shadow-sm">
            <h2 className="text-lg font-semibold mb-2">🚀 Laravel 12</h2>
            <p className="text-sm text-muted-foreground">PHP 8.2 Backend</p>
          </div>
          <div className="bg-card border border-border rounded-lg p-6 shadow-sm">
            <h2 className="text-lg font-semibold mb-2">⚛️ React + TypeScript</h2>
            <p className="text-sm text-muted-foreground">Vite Frontend</p>
          </div>
          <div className="bg-card border border-border rounded-lg p-6 shadow-sm">
            <h2 className="text-lg font-semibold mb-2">🐘 PostgreSQL</h2>
            <p className="text-sm text-muted-foreground">Database</p>
          </div>
          <div className="bg-card border border-border rounded-lg p-6 shadow-sm">
            <h2 className="text-lg font-semibold mb-2">🔴 Redis</h2>
            <p className="text-sm text-muted-foreground">Cache & Queue</p>
          </div>
        </div>
        <div className="mt-8">
          <p className="text-sm text-muted-foreground">Phase 1: Foundation Complete ✅</p>
          <p className="text-xs text-muted-foreground mt-2">Next: Authentication & RBAC System</p>
        </div>
      </div>
    </div>
  )
}

export default App
