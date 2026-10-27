import { useEffect } from 'react';
import { useLocation, Link } from 'react-router-dom';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import './Legal.css';

export default function Legal() {
  const { hash } = useLocation();

  useEffect(() => {
    if (hash) {
      const el = document.getElementById(hash.replace('#', ''));
      el?.scrollIntoView({ behavior: 'smooth' });
    } else {
      window.scrollTo(0, 0);
    }
  }, [hash]);

  return (
    <>
      <Navbar forceSolid />
      <main className="legal">
        <div className="legal__inner">
          <p className="legal__eyebrow">Evently</p>
          <h1 className="legal__title">About & policies</h1>
          <p className="legal__lede">
            Evently is a Ugandan marketplace connecting event planners with
            verified service providers — caterers, decorators, photographers,
            DJs, MCs, and venues.
          </p>

          <section id="about" className="legal__section">
            <h2>About</h2>
            <p>
              Evently was built to replace the informal, word-of-mouth way
              event services are booked in Uganda. Instead of chasing
              WhatsApp forwards and phone numbers, clients compare verified
              providers with real portfolios, transparent pricing, and secure
              mobile money payments.
            </p>
            <p>
              Every provider profile on Evently is reviewed before it goes
              live. Every review is tied to a completed booking. Every
              payment is protected until the booking is delivered.
            </p>
            <p>
              We are based in Kampala and currently serving Uganda. Questions,
              feedback, or partnership enquiries:{' '}
              <a href="mailto:hello@evently.ug">hello@evently.ug</a>.
            </p>
          </section>

          <section id="privacy" className="legal__section">
            <h2>Privacy</h2>
            <p>
              We collect only what is needed to run the marketplace: your
              name, email, phone number, and booking details. We use this to
              connect clients with providers, process payments, and send
              booking notifications.
            </p>
            <p>
              Payments are processed by <strong>MarzPay</strong>, our mobile
              money partner. Evently does not store card details. Phone
              numbers used for payment are shared with MarzPay only for the
              duration of the transaction.
            </p>
            <p>
              We never sell your data. We never share it with third parties
              except as required to complete a booking or comply with the
              law.
            </p>
          </section>

          <section id="terms" className="legal__section">
            <h2>Terms of use</h2>
            <p>
              By using Evently you agree to provide accurate information,
              honour confirmed bookings, and treat other users with respect.
            </p>
            <p>
              Providers are independent businesses. Evently facilitates the
              booking and payment, but the service itself is delivered by the
              provider. Disputes are resolved through our support team and,
              where necessary, the payment provider's dispute process.
            </p>
            <p>
              Accounts that misrepresent their services, harass other users,
              or attempt to circumvent payment may be suspended.
            </p>
          </section>

          <div className="legal__back">
            <Link to="/" className="ui-btn ui-btn--ghost">← Back to home</Link>
          </div>
        </div>
      </main>
      <Footer />
    </>
  );
}