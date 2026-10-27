import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import api from '../api/axios';
import './Showcase.css';

const AUTOPLAY_MS = 5000;

export default function Showcase() {
  const trackRef = useRef(null);
  const [slides, setSlides] = useState([]);
  const [loading, setLoading] = useState(true);
  const [paused, setPaused] = useState(false);
  const [reducedMotion, setReducedMotion] = useState(false);

  useEffect(() => {
    api.get('/api/showcase')
      .then((res) => setSlides(res.data || []))
      .catch(() => setSlides([]))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
    setReducedMotion(mq.matches);
    const handler = (e) => setReducedMotion(e.matches);
    mq.addEventListener('change', handler);
    return () => mq.removeEventListener('change', handler);
  }, []);

  useEffect(() => {
    if (reducedMotion || paused || slides.length < 3) return;
    const track = trackRef.current;
    if (!track) return;

    const id = setInterval(() => {
      const slide = track.querySelector('.showcase__slide');
      if (!slide) return;
      const step = slide.offsetWidth + 24;
      const maxScroll = track.scrollWidth - track.clientWidth;

      if (track.scrollLeft >= maxScroll - 8) {
        track.scrollTo({ left: 0, behavior: 'smooth' });
      } else {
        track.scrollBy({ left: step, behavior: 'smooth' });
      }
    }, AUTOPLAY_MS);

    return () => clearInterval(id);
  }, [paused, reducedMotion, slides.length]);

  useEffect(() => {
    function onVisibility() { setPaused(document.hidden); }
    document.addEventListener('visibilitychange', onVisibility);
    return () => document.removeEventListener('visibilitychange', onVisibility);
  }, []);

  function scrollBy(direction) {
    const track = trackRef.current;
    if (!track) return;
    const slide = track.querySelector('.showcase__slide');
    const step = slide ? slide.offsetWidth + 24 : track.clientWidth * 0.8;
    track.scrollBy({ left: direction * step, behavior: 'smooth' });
  }

  // Hide the whole section if there's nothing to show
  if (!loading && slides.length === 0) return null;

  return (
    <section className="showcase">
      <div className="showcase__inner">
        <header className="showcase__head">
          <div>
            <p className="showcase__kicker">Recent work</p>
            <h2 className="showcase__heading">Real events, real providers.</h2>
          </div>
          <div className="showcase__nav">
            <button
              type="button"
              className="showcase__nav-btn"
              onClick={() => scrollBy(-1)}
              aria-label="Previous"
            >←</button>
            <button
              type="button"
              className="showcase__nav-btn"
              onClick={() => scrollBy(1)}
              aria-label="Next"
            >→</button>
          </div>
        </header>

        {loading ? (
          <div className="showcase__loading">Loading recent work…</div>
        ) : (
          <motion.div
            className="showcase__track"
            ref={trackRef}
            onMouseEnter={() => setPaused(true)}
            onMouseLeave={() => setPaused(false)}
            onTouchStart={() => setPaused(true)}
            onTouchEnd={() => setPaused(false)}
            initial={{ opacity: 0, y: 24 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, amount: 0.2 }}
            transition={{ duration: 0.7, ease: [0.22, 1, 0.36, 1] }}
          >
            {slides.map((s) => (
              <Link
                key={s.id}
                to={`/providers/${s.provider.id}`}
                className="showcase__slide"
              >
                <div className="showcase__media">
                  {s.media_type === 'video' ? (
                    <video src={`/storage/${s.image_path}`} muted playsInline preload="metadata" />
                  ) : (
                    <img src={`/storage/${s.image_path}`} alt={s.caption || s.provider.business_name} loading="lazy" />
                  )}
                </div>
                <div className="showcase__caption">
                  <span className="showcase__tag">
                    {s.provider.category} · {s.provider.location?.split(',')[0]}
                  </span>
                  <span className="showcase__label">
                    {s.caption || s.provider.business_name}
                  </span>
                </div>
              </Link>
            ))}
          </motion.div>
        )}
      </div>
    </section>
  );
}