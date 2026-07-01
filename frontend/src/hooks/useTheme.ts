import { useContext } from 'react';
import { ThemeContext } from '@/context/ThemeContext';

/**
 * Custom hook to use Theme Context
 * 
 * @throws Error if used outside ThemeProvider
 */
export const useTheme = () => {
  const context = useContext(ThemeContext);
  
  if (context === undefined) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  
  return context;
};
