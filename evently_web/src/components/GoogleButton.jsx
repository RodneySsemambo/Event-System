import { FcGoogle } from 'react-icons/fc';
import './GoogleButton.css';

const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000'; 

export default function GoogleButton({ label = 'Continue with Google' }) {
  function handleClick() {
    window.location.href = `${API_BASE}/auth/google/redirect`;
  }

  return (
    <button type="button" className="google-btn" onClick={handleClick}>
      <FcGoogle size={20} />
      <span>{label}</span>
    </button>
  );
}