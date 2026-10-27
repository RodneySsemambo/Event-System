import { Turnstile } from '@marsidev/react-turnstile';
import { useRef } from 'react';
import './TurnstileWidget.css';

const SITE_KEY = import.meta.env.VITE_TURNSTILE_SITE_KEY;

export default function TurnstileWidget({ onVerify, onExpire }) {
  const ref = useRef(null);

  if (!SITE_KEY) {
    // Dev fallback: if no key, auto-verify so login isn't blocked
    if (onVerify) setTimeout(() => onVerify('dev-bypass'), 0);
    return null;
  }

  return (
    <div className="turnstile-widget">
      <Turnstile
        ref={ref}
        siteKey={SITE_KEY}
        options={{ theme: 'light', size: 'flexible' }}
        onSuccess={(token) => onVerify?.(token)}
        onExpire={() => onExpire?.()}
        onError={() => onExpire?.()}
      />
    </div>
  );
}