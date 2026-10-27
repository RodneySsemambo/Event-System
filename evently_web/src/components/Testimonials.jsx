import { motion } from 'framer-motion';
import './Testimonials.css';

// PLACEHOLDER DATA — replace with real reviews fetched from
// GET /api/providers/{id}/reviews once the reviews feature is live.
// Structure matches the eventual API shape: { id, quote, name, role,
// event_type, rating }.
const testimonials = [
  {
    id: 1,
    quote: 'Finding a decorator in Kampala used to mean three days of WhatsApp groups. On Evently I compared six in an hour and picked the one whose portfolio actually matched what I wanted.',
    name: 'Nakato S.',
    role: 'Wedding — Kampala',
    rating: 5,
  },
  {
    id: 2,
    quote: 'The provider responded in minutes. Clear pricing, no haggling, and the setup on the day was exactly what the photos showed.',
    name: 'Daniel M.',
    role: 'Corporate launch — Entebbe',
    rating: 5,
  },
  {
    id: 3,
    quote: 'As a caterer, this is the first channel where I can show real work instead of relying on word of mouth.',
    name: 'Chef Aisha K.',
    role: 'Provider — Ntinda',
    rating: 5,
  },
  {
    id: 4,
    quote: 'Paid through mobile money, got a confirmation straight away. The whole thing felt trusted.',
    name: 'Robert O.',
    role: 'Birthday — Bukoto',
    rating: 4,
  },
];

const container = {
  hidden: {},
  show: { transition: { staggerChildren: 0.08 } },
};

const rise = {
  hidden: { opacity: 0, y: 20 },
  show: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.6, ease: [0.22, 1, 0.36, 1] },
  },
};

export default function Testimonials() {
  return (
    <section className="testimonials" id="testimonials">
      <div className="testimonials__inner">
        <header className="testimonials__head">
          <div>
            <p className="testimonials__kicker">What people say</p>
            <h2 className="testimonials__heading">Reviews from real events.</h2>
          </div>
          <p className="testimonials__lead">
            Every review is tied to a completed booking. No anonymous ratings, no
            paid placements.
          </p>
        </header>

        <motion.div
          className="testimonials__grid"
          variants={container}
          initial="hidden"
          whileInView="show"
          viewport={{ once: true, amount: 0.15 }}
        >
          {/* Left — aggregate panel */}
          <motion.aside className="testimonials__panel" variants={rise}>
            <span className="testimonials__panel-label">Average rating</span>
            <div className="testimonials__score">
              <span className="testimonials__score-num">4.9</span>
              <span className="testimonials__score-of">/ 5</span>
            </div>
            <div className="testimonials__stars" aria-label="4.9 out of 5">
              {'★★★★★'}
            </div>
            <p className="testimonials__panel-meta">
              Based on completed bookings
            </p>

            <div className="testimonials__bars">
              <Bar label="5★" pct={91} />
              <Bar label="4★" pct={7} />
              <Bar label="3★" pct={2} />
            </div>

            <p className="testimonials__panel-note">
              Only clients with a completed booking can leave a review.
            </p>
          </motion.aside>

          {/* Right — testimonial mosaic */}
          <div className="testimonials__list">
            {testimonials.map((t) => (
              <motion.figure
                key={t.id}
                className="testimonial"
                variants={rise}
              >
                <div className="testimonial__stars">
                  {'★'.repeat(t.rating)}
                  <span className="testimonial__stars--dim">
                    {'★'.repeat(5 - t.rating)}
                  </span>
                </div>
                <blockquote className="testimonial__quote">
                  “{t.quote}”
                </blockquote>
                <figcaption className="testimonial__by">
                  <span className="testimonial__name">{t.name}</span>
                  <span className="testimonial__role">{t.role}</span>
                </figcaption>
              </motion.figure>
            ))}
          </div>
        </motion.div>
      </div>
    </section>
  );
}

function Bar({ label, pct }) {
  return (
    <div className="testimonials__bar-row">
      <span className="testimonials__bar-label">{label}</span>
      <div className="testimonials__bar-track">
        <div className="testimonials__bar-fill" style={{ width: `${pct}%` }} />
      </div>
      <span className="testimonials__bar-pct">{pct}%</span>
    </div>
  );
}