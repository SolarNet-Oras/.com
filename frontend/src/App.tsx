import React from 'react';
import './index.css';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

// ============================================================================
// Type Definitions
// ============================================================================

interface HomePageProps {}

interface TechnologyCardProps {
  emoji: string;
  title: string;
  description: string;
}

// ============================================================================
// Components
// ============================================================================

/**
 * Technology Card Component
 * Displays a single technology used in the stack
 */
const TechnologyCard: React.FC<TechnologyCardProps> = ({ emoji, title, description }) => {
  return (
    <div className="bg-card border border-border rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
      <h2 className="text-lg font-semibold mb-2">
        {emoji} {title}
      </h2>
      <p className="text-sm text-muted-foreground">{description}</p>
    </div>
  );
};

/**
 * Home Page Component
 * Landing page showing the technology stack
 */
const HomePage: React.FC<HomePageProps> = () => {
  const technologies: TechnologyCardProps[] = [
    {
      emoji: '🚀',
      title: 'Laravel 12',
      description: 'PHP 8.2 Backend',
    },
    {
      emoji: '⚛️',
      title: 'React + TypeScript',
      description: 'Vite Frontend',
    },
    {
      emoji: '🐘',
      title: 'PostgreSQL',
      description: 'Database',
    },
    {
      emoji: '🔴',
      title: 'Redis',
      description: 'Cache & Queue',
    },
  ];

  return (
    <div className="flex items-center justify-center min-h-screen">
      <div className="text-center space-y-6 p-8">
        <h1 className="text-4xl font-bold text-foreground">
          ISP Billing & Network Management System
        </h1>
        <p className="text-xl text-muted-foreground">
          MikroTik IPoE Management Platform
        </p>
        
        <div className="flex flex-wrap gap-4 justify-center mt-8">
          {technologies.map((tech) => (
            <TechnologyCard
              key={tech.title}
              emoji={tech.emoji}
              title={tech.title}
              description={tech.description}
            />
          ))}
        </div>

        <div className="mt-8">
          <p className="text-sm text-muted-foreground">
            Phase 1: Foundation Complete ✅
          </p>
          <p className="text-xs text-muted-foreground mt-2">
            Next: Phase 2 - Authentication & RBAC System
          </p>
        </div>

        <div className="mt-6 p-4 bg-secondary rounded-lg">
          <h3 className="text-sm font-semibold mb-2">Security Enhancements Applied:</h3>
          <ul className="text-xs text-muted-foreground text-left space-y-1">
            <li>✅ Secure token storage (sessionStorage)</li>
            <li>✅ Full TypeScript type coverage</li>
            <li>✅ HTTP status constants</li>
            <li>✅ Production-ready architecture</li>
          </ul>
        </div>
      </div>
    </div>
  );
};

// ============================================================================
// Main App Component
// ============================================================================

const App: React.FC = (): JSX.Element => {
  return (
    <Router>
      <div className="App min-h-screen bg-background">
        <Routes>
          <Route path="/" element={<HomePage />} />
        </Routes>
      </div>
    </Router>
  );
};

export default App;
