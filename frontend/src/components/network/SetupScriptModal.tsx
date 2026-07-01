import { useState } from 'react';
import { routerService } from '@/services/routerService';
import { X, Copy, Check, FileCode } from 'lucide-react';

interface SetupScriptModalProps {
  isOpen: boolean;
  onClose: () => void;
  routerId: string;
  routerName: string;
}

export function SetupScriptModal({ isOpen, onClose, routerId, routerName }: SetupScriptModalProps) {
  const [script, setScript] = useState<string>('');
  const [loading, setLoading] = useState(false);
  const [copied, setCopied] = useState(false);
  const [billingIp, setBillingIp] = useState('');

  const generateScript = async () => {
    setLoading(true);
    try {
      const response = await routerService.generateSetupScript(routerId, billingIp || undefined);
      setScript(response.script);
    } catch (error) {
      console.error('Failed to generate script:', error);
      alert('Failed to generate script');
    } finally {
      setLoading(false);
    }
  };

  const copyToClipboard = () => {
    navigator.clipboard.writeText(script);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const downloadScript = () => {
    const blob = new Blob([script], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `mikrotik-setup-${routerName.replace(/\s+/g, '-').toLowerCase()}.rsc`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50" onClick={onClose}>
      <div className="bg-card rounded-lg shadow-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden" onClick={(e) => e.stopPropagation()}>
        <div className="flex items-center justify-between p-6 border-b border-border">
          <div className="flex items-center space-x-3">
            <FileCode className="h-6 w-6 text-primary" />
            <div>
              <h2 className="text-xl font-bold text-foreground">MikroTik Setup Script Generator</h2>
              <p className="text-sm text-muted-foreground">Router: {routerName}</p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-2 hover:bg-secondary rounded transition-colors"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        <div className="p-6 space-y-4">
          {!script ? (
            <div className="space-y-4">
              <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h3 className="font-semibold text-blue-900 dark:text-blue-200 mb-2">What does this script do?</h3>
                <ul className="text-sm text-blue-800 dark:text-blue-300 space-y-1 list-disc list-inside">
                  <li>Creates a dedicated API user for billing system access</li>
                  <li>Enables RouterOS API service on configured port</li>
                  <li>Configures firewall rules (if you provide billing system IP)</li>
                  <li>Sets up address lists for customer management</li>
                  <li>Tests API connectivity</li>
                </ul>
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Billing System IP Address (Optional)
                </label>
                <input
                  type="text"
                  value={billingIp}
                  onChange={(e) => setBillingIp(e.target.value)}
                  placeholder="e.g., 203.0.113.10 (leave empty to skip firewall rules)"
                  className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                />
                <p className="text-xs text-muted-foreground mt-1">
                  Provide your billing server's public IP to automatically create firewall rules
                </p>
              </div>

              <button
                onClick={generateScript}
                disabled={loading}
                className="w-full px-4 py-3 bg-primary text-primary-foreground rounded-md hover:opacity-90 transition-opacity disabled:opacity-50 font-medium"
              >
                {loading ? 'Generating Script...' : 'Generate Setup Script'}
              </button>
            </div>
          ) : (
            <div className="space-y-4">
              <div className="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <h3 className="font-semibold text-yellow-900 dark:text-yellow-200 mb-2">📋 How to use this script:</h3>
                <ol className="text-sm text-yellow-800 dark:text-yellow-300 space-y-1 list-decimal list-inside">
                  <li>Connect to your MikroTik router via <strong>Winbox</strong> or <strong>SSH</strong></li>
                  <li>Open <strong>"New Terminal"</strong> window (in Winbox)</li>
                  <li>Copy the script below (click Copy button)</li>
                  <li>Paste into terminal and press <strong>Enter</strong></li>
                  <li>Wait for "Setup Complete" message</li>
                  <li>Test connection in billing dashboard</li>
                </ol>
              </div>

              <div className="relative">
                <div className="absolute top-2 right-2 flex space-x-2">
                  <button
                    onClick={downloadScript}
                    className="px-3 py-1.5 bg-secondary text-foreground rounded-md hover:bg-secondary/80 transition-colors text-sm flex items-center space-x-1"
                  >
                    <FileCode className="h-4 w-4" />
                    <span>Download .rsc</span>
                  </button>
                  <button
                    onClick={copyToClipboard}
                    className="px-3 py-1.5 bg-primary text-primary-foreground rounded-md hover:opacity-90 transition-opacity text-sm flex items-center space-x-1"
                  >
                    {copied ? (
                      <>
                        <Check className="h-4 w-4" />
                        <span>Copied!</span>
                      </>
                    ) : (
                      <>
                        <Copy className="h-4 w-4" />
                        <span>Copy</span>
                      </>
                    )}
                  </button>
                </div>
                <pre className="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-xs font-mono max-h-[400px] overflow-y-auto">
                  <code>{script}</code>
                </pre>
              </div>

              <div className="flex space-x-3">
                <button
                  onClick={() => setScript('')}
                  className="flex-1 px-4 py-2 border border-border rounded-md text-foreground hover:bg-secondary transition-colors"
                >
                  Generate New Script
                </button>
                <button
                  onClick={onClose}
                  className="flex-1 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:opacity-90 transition-opacity"
                >
                  Done
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
