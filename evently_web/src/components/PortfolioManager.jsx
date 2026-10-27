import { useState, useRef } from 'react';
import {
  uploadPortfolioItem,
  updatePortfolioItem,
  deletePortfolioItem,
  reorderPortfolio,
} from '../api/providers';
import './PortfolioManager.css';

const STORAGE_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export default function PortfolioManager({ providerId, items, onChange }) {
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState(null);
  const fileInputRef = useRef(null);

  async function handleUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    setUploading(true);
    setError(null);
    try {
      const item = await uploadPortfolioItem(providerId, file);
      onChange([...items, item]);
    } catch (err) {
      setError(err.response?.data?.message || 'Upload failed. Check file type/size (max 5MB).');
    } finally {
      setUploading(false);
      e.target.value = '';
    }
  }

  async function handleCaptionBlur(item, newCaption) {
    if (newCaption === item.caption) return;
    const updated = await updatePortfolioItem(item.id, newCaption);
    onChange(items.map((i) => (i.id === item.id ? updated : i)));
  }

  async function handleDelete(item) {
    if (!window.confirm('Delete this portfolio image?')) return;
    await deletePortfolioItem(item.id);
    onChange(items.filter((i) => i.id !== item.id));
  }

  async function move(index, direction) {
    const target = index + direction;
    if (target < 0 || target >= items.length) return;

    const reordered = [...items];
    [reordered[index], reordered[target]] = [reordered[target], reordered[index]];
    onChange(reordered);

    const saved = await reorderPortfolio(providerId, reordered.map((i) => i.id));
    onChange(saved);
  }

  return (
    <section className="portfolio">
      <header className="portfolio__head">
        <div>
          <h2 className="portfolio__title">Portfolio</h2>
          <p className="ui-empty__text">
  Add photos or short videos (MP4/WebM/MOV, up to 50MB). The first item is what clients see first.
</p>
        </div>

        <button
          type="button"
          className="ui-btn ui-btn--primary"
          onClick={() => fileInputRef.current?.click()}
          disabled={uploading}
        >
          {uploading ? 'Uploading…' : '+ Add photo'}
        </button>

        <input
          ref={fileInputRef}
          type="file"
           accept="image/png,image/jpeg,image/webp,video/mp4,video/webm,video/quicktime"
          onChange={handleUpload}
          disabled={uploading}
          hidden
        />
      </header>

      {error && <div className="ui-alert ui-alert--error">{error}</div>}

      {items.length === 0 ? (
        <div className="portfolio__empty">
          <p className="ui-empty__title">No photos yet.</p>
          <p className="ui-empty__text">
            Add your first portfolio image to show clients what you can do.
          </p>
        </div>
      ) : (
        <div className="portfolio__grid">
          {items.map((item, index) => (
            <article key={item.id} className="portfolio__item">
              <div className="portfolio__item-media">
                <img
                  src={`${STORAGE_BASE}/storage/${item.image_path}`}
                  alt={item.caption || 'Portfolio item'}
                  loading="lazy"
                />
                {index === 0 && <span className="portfolio__item-first">First</span>}
              </div>

              <input
                type="text"
                defaultValue={item.caption || ''}
                placeholder="Add a caption…"
                onBlur={(e) => handleCaptionBlur(item, e.target.value)}
                className="portfolio__item-caption"
              />

              <div className="portfolio__item-actions">
                <div className="portfolio__item-move">
                  <button
                    type="button"
                    className="portfolio__move-btn"
                    onClick={() => move(index, -1)}
                    disabled={index === 0}
                    aria-label="Move up"
                  >↑</button>
                  <button
                    type="button"
                    className="portfolio__move-btn"
                    onClick={() => move(index, 1)}
                    disabled={index === items.length - 1}
                    aria-label="Move down"
                  >↓</button>
                </div>
                <button
                  type="button"
                  className="portfolio__delete"
                  onClick={() => handleDelete(item)}
                >
                  Delete
                </button>
              </div>
              <div className="portfolio__item-media">
  {item.media_type === 'video' ? (
    <video
      src={`${STORAGE_BASE}/storage/${item.image_path}`}
      muted
      playsInline
      preload="metadata"
    />
  ) : (
    <img
      src={`${STORAGE_BASE}/storage/${item.image_path}`}
      alt={item.caption || 'Portfolio item'}
      loading="lazy"
    />
  )}
  {index === 0 && <span className="portfolio__item-first">First</span>}
  {item.media_type === 'video' && <span className="portfolio__item-type">Video</span>}
</div>
            </article>
          ))}
        </div>
      )}
    </section>
  );
}