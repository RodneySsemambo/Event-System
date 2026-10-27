import { motion } from 'framer-motion';
import './ImageTextSection.css';

// Placeholder photo — Picsum's placeholder-image service (seeded, so it's
// stable across reloads). Swap for real provider/event photography before
// launch, same as the hero video.
const PLACEHOLDER_IMAGE = 'https://picsum.photos/seed/evently-decor/900/720';

export default function ImageTextSection() {
  return (
    <section className="image-text">
      <div className="image-text__inner">
        <motion.div
          className="image-text__media"
          initial={{ opacity: 0, x: -32 }}
          whileInView={{ opacity: 1, x: 0 }}
          viewport={{ once: true, amount: 0.4 }}
          transition={{ duration: 0.7, ease: [0.22, 1, 0.36, 1] }}
        >
          <img src={PLACEHOLDER_IMAGE} alt="A decorator arranging table settings for an event" />
          <div className="image-text__floatcard">
            <span className="image-text__floatcard-rating">4.9</span>
            <span className="image-text__floatcard-label">Average provider rating</span>
          </div>
        </motion.div>

        <div className="image-text__copy">
          <h2>Verified providers, not a guessing game.</h2>
          <p>
            Every profile on Evently shows real past work, a clear price range, and how
            fast they typically respond — so you can compare properly before you ever
            send a message.
          </p>
          <ul className="image-text__points">
            <li>Portfolio photos from real, completed events</li>
            <li>Location and category filters that actually narrow things down</li>
            <li>Direct messaging, no phone-tag required</li>
          </ul>
        </div>
      </div>
    </section>
  );
}
