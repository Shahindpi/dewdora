export type AnalyticsParameters = Record<string, string | number | undefined>;
declare global { interface Window { gtag?: (...args: unknown[]) => void; dataLayer?: unknown[]; } }
export function trackEvent(name: string, parameters: AnalyticsParameters) {
  const payload = Object.fromEntries(Object.entries(parameters).filter(([, value]) => value !== undefined));
  if (process.env.NODE_ENV === "development") console.info("[Dewdora analytics]", name, payload);
  window.gtag?.("event", name, payload);
}
