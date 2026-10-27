import './AuthDivider.css';

export default function AuthDivider({ text = 'or' }) {
  return (
    <div className="auth-divider">
      <span className="auth-divider__line" />
      <span className="auth-divider__text">{text}</span>
      <span className="auth-divider__line" />
    </div>
  );
}