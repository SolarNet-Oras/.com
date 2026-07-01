/**
 * Error Logging Utility
 * 
 * Centralized error logging for production-ready error handling.
 * Can be easily replaced with services like Sentry, LogRocket, etc.
 */

type ErrorLevel = 'error' | 'warn' | 'info';

interface ErrorLogOptions {
  level?: ErrorLevel;
  context?: Record<string, unknown>;
  userId?: string;
}

class ErrorLogger {
  private isDevelopment = import.meta.env.DEV;

  /**
   * Log error with context
   */
  log(message: string, error?: unknown, options: ErrorLogOptions = {}): void {
    const { level = 'error', context = {} } = options;

    // In development, use console
    if (this.isDevelopment) {
      const logFn = console[level] || console.error;
      logFn(`[${level.toUpperCase()}] ${message}`, error, context);
      return;
    }

    // In production, send to monitoring service
    // TODO: Replace with actual monitoring service (Sentry, LogRocket, etc.)
    this.sendToMonitoring(message, error, options);
  }

  /**
   * Log error
   */
  error(message: string, error?: unknown, context?: Record<string, unknown>): void {
    this.log(message, error, { level: 'error', context });
  }

  /**
   * Log warning
   */
  warn(message: string, context?: Record<string, unknown>): void {
    this.log(message, undefined, { level: 'warn', context });
  }

  /**
   * Log info
   */
  info(message: string, context?: Record<string, unknown>): void {
    this.log(message, undefined, { level: 'info', context });
  }

  /**
   * Send to monitoring service (production)
   */
  private sendToMonitoring(
    message: string,
    error: unknown,
    options: ErrorLogOptions
  ): void {
    // Production error monitoring integration point
    // Example integrations:
    
    // Sentry:
    // Sentry.captureException(error, {
    //   level: options.level,
    //   tags: options.context,
    // });

    // LogRocket:
    // LogRocket.captureException(error as Error, {
    //   tags: options.context,
    // });

    // Custom API:
    // fetch('/api/v1/logs', {
    //   method: 'POST',
    //   body: JSON.stringify({
    //     message,
    //     error: error instanceof Error ? error.message : String(error),
    //     level: options.level,
    //     context: options.context,
    //     timestamp: new Date().toISOString(),
    //   }),
    // });

    // For now, silent in production (replace with actual service)
    // console.error is removed for production
  }
}

// Export singleton instance
export const logger = new ErrorLogger();
