import { motion } from 'framer-motion';
import { Link } from 'react-router-dom';
import './HowItWorks.css';

const steps = [
  {
    n: '01',
    title: 'Search and compare',
    body: 'Filter by category, location, and price range. Browse real portfolios and pricing before you reach out to anyone.',
  },
  {
    n: '02',
    title: 'Message directly',
    body: 'Ask about availability, pricing specifics, or anything else — straight to the provider, no middleman, no waiting on a phone call.',
  },
  {
    n: '03',
    title: 'Book with confidence',
    body: 'Send a booking request with your event details. The provider confirms, payment is secured, and the date is locked in.',
  },
];

export default function HowItWorks() {
  return (
    <section className="how" id="how-it-works">
      <div className="how__inner">
        <header className="how__head">
          <div className="how__head-left">
            <p className="how__kicker">How Evently works</p>
            <h2 className="how__heading">
              From search to booked,<br />in three steps.
            </h2>
          </div>
          <p className="how__lead">
            No middlemen, no guesswork, no chasing phone numbers. Just
            verified providers and a clear path from browsing to a
            confirmed booking.
          </p>
        </header>

        <div className="how__steps">
          {steps.map((step, i) => (
            <motion.article
              key={step.n}
              className="how__step"
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, amount: 0.4 }}
              transition={{
                duration: 0.6,
                delay: i * 0.12,
                ease: [0.22, 1, 0.36, 1],
              }}
            >
              <span className="how__step-num">{step.n}</span>
              <h3 className="how__step-title">{step.title}</h3>
              <p className="how__step-body">{step.body}</p>
            </motion.article>
          ))}
        </div>

        <motion.div
          className="how__cta"
          initial={{ opacity: 0 }}
          whileInView={{ opacity: 1 }}
          viewport={{ once: true, amount: 0.6 }}
          transition={{ duration: 0.6, delay: 0.3, ease: [0.22, 1, 0.36, 1] }}
        >
          <span className="how__cta-text">Ready to find your provider?</span>
          <Link to="/search" className="how__cta-link">
            Browse providers <span aria-hidden="true">→</span>
          </Link>
        </motion.div>
      </div>
    </section>
  );
}