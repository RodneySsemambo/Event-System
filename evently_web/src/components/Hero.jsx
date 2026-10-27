import { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import { Link, useNavigate } from 'react-router-dom';
import './Hero.css';

const HERO_VIDEO = '/videos/hero.mp4';

const CATEGORIES = [
  { value: '', label: 'Any service' },
  { value: 'catering', label: 'Catering' },
  { value: 'decoration', label: 'Decoration' },
  { value: 'photography', label: 'Photography' },
  { value: 'dj', label: 'DJ' },
  { value: 'mc', label: 'MC' },
  { value: 'venue', label: 'Venue' },
];

const container = {
  hidden: {},
  show: { transition: { staggerChildren: 0.12, delayChildren: 0.2 } },
};
const rise = {
  hidden: { opacity: 0, y: 24 },
  show: { opacity: 1, y: 0, transition: { duration: 0.7, ease: [0.22, 1, 0.36, 1] } },
};

export default function Hero() {
  const navigate = useNavigate();
  const [reducedMotion, setReducedMotion] = useState(false);
  const [category, setCategory] = useState('');
  const [location, setLocation] = useState('');
  const [date, setDate] = useState('');

  useEffect(() => {
    const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
    setReducedMotion(mq.matches);
    const handler = (e) => setReducedMotion(e.matches);
    mq.addEventListener('change', handler);
    return () => mq.removeEventListener('change', handler);
  }, []);

  const handleSearch = (e) => {
    e.preventDefault();
    const params = new URLSearchParams();
    if (category) params.set('category', category);
    if (location) params.set('location', location);
    if (date) params.set('date', date);
    navigate(`/search${params.toString() ? `?${params}` : ''}`);
  };

  return (
    <section className="hero">
      <video
        className="hero__video"
        autoPlay={!reducedMotion}
        muted
        loop
        playsInline
        preload="metadata"
        poster="/gallery/gallery-1.jpg"
      >
        <source src={HERO_VIDEO} type="video/mp4" />
      </video>

      <div className="hero__scrim" />

      <motion.div
        className="hero__content"
        variants={container}
        initial="hidden"
        animate="show"
      >
        <div className="hero__inner">
          <motion.p className="hero__kicker" variants={rise}>
            Event services, found in minutes
          </motion.p>

          <motion.h1 className="hero__headline" variants={rise}>
            Every provider your<br />next event needs.
          </motion.h1>

          <motion.p className="hero__sub" variants={rise}>
            Caterers, decorators, photographers, DJs, and MCs — verified,
            searchable, and a booking away.
          </motion.p>

          <motion.form className="hero__search" variants={rise} onSubmit={handleSearch}>
            <div className="hero__search-field">
              <label className="hero__search-label" htmlFor="hero-category">Service</label>
              <select
                id="hero-category"
                className="hero__search-input"
                value={category}
                onChange={(e) => setCategory(e.target.value)}
              >
                {CATEGORIES.map((c) => (
                  <option key={c.value} value={c.value}>{c.label}</option>
                ))}
              </select>
            </div>

            <div className="hero__search-field">
              <label className="hero__search-label" htmlFor="hero-location">Location</label>
              <input
                id="hero-location"
                className="hero__search-input"
                type="text"
                placeholder="Kampala"
                value={location}
                onChange={(e) => setLocation(e.target.value)}
              />
            </div>

            <div className="hero__search-field">
              <label className="hero__search-label" htmlFor="hero-date">Date</label>
              <input
                id="hero-date"
                className="hero__search-input"
                type="date"
                value={date}
                onChange={(e) => setDate(e.target.value)}
              />
            </div>

            <button type="submit" className="hero__search-btn">
              Search
            </button>
          </motion.form>

          <motion.div className="hero__trust" variants={rise}>
            <span className="hero__trust-item">✓ Verified providers</span>
            <span className="hero__trust-item">✓ Mobile money</span>
            <span className="hero__trust-item">✓ Free to browse</span>
          </motion.div>

          <motion.p className="hero__provider-link" variants={rise}>
            Are you a provider?{' '}
            <Link to="/register" className="hero__link">List your services</Link>
          </motion.p>
        </div>
      </motion.div>
    </section>
  );
}