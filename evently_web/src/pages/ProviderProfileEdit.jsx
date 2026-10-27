import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Navbar from '../components/Navbar';
import { useAuth } from '../context/AuthContext';
import { fetchCategories, updateProviderProfile } from '../api/providers';
import PortfolioManager from '../components/PortfolioManager';
import './ProviderProfileEdit.css';

const STORAGE_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export default function ProviderProfileEdit() {
  const { user, refreshUser } = useAuth();
  const provider = user.provider_profile;

  const [categories, setCategories] = useState([]);
  const [form, setForm] = useState({
    business_name: provider.business_name || '',
    category_id: provider.category_id || '',
    location: provider.location || '',
    whatsapp_number: provider.whatsapp_number || '',
    bio: provider.bio || '',
    price_range_min: provider.price_range_min || '',
    price_range_max: provider.price_range_max || '',
    availability_status: provider.availability_status || 'available',
  });
  const [photoFile, setPhotoFile] = useState(null);
  const [bannerFile, setBannerFile] = useState(null);
  const [portfolioItems, setPortfolioItems] = useState(provider.portfolio_items || []);
  const [saving, setSaving] = useState(false);
  const [errors, setErrors] = useState({});
  const [savedMessage, setSavedMessage] = useState(null);

  useEffect(() => {
    fetchCategories().then(setCategories);
  }, []);

  function updateField(e) {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setSaving(true);
    setErrors({});
    setSavedMessage(null);

    try {
      await updateProviderProfile(provider.id, form, {
        profile_photo: photoFile,
        banner: bannerFile,
      });
      await refreshUser();
      setSavedMessage('Profile saved.');
      setPhotoFile(null);
      setBannerFile(null);
    } catch (err) {
      if (err.response?.status === 422) {
        setErrors(err.response.data.errors || {});
      } else {
        setErrors({ general: ['Something went wrong. Please try again.'] });
      }
    } finally {
      setSaving(false);
    }
  }

  const fieldError = (name) => errors[name]?.[0];

  return (
    <>
      <Navbar forceSolid />
      <main className="ui-page">
        <div className="ui-container--form">
          <header className="edit__head">
            <div>
              <p className="ui-eyebrow">Provider</p>
              <h1 className="ui-title">Edit your profile</h1>
              <p className="ui-sub">
                This is what clients see on your public page. Keep it up to date.
              </p>
            </div>
            <Link to={`/providers/${provider.id}`} className="ui-btn ui-btn--ghost ui-btn--sm">
              View public page →
            </Link>
          </header>

          {fieldError('general') && (
            <div className="ui-alert ui-alert--error">{fieldError('general')}</div>
          )}
          {savedMessage && (
            <div className="ui-alert ui-alert--success">{savedMessage}</div>
          )}

          <form onSubmit={handleSubmit} className="edit__form">
            <section className="edit__section">
              <h2 className="edit__section-title">Business details</h2>

              <label className="ui-field">
                <span className="ui-field__label">Business name</span>
                <input
                  type="text"
                  name="business_name"
                  className="ui-field__input"
                  value={form.business_name}
                  onChange={updateField}
                />
                {fieldError('business_name') && (
                  <p className="ui-field__error">{fieldError('business_name')}</p>
                )}
              </label>

              <label className="ui-field">
                <span className="ui-field__label">Category</span>
                <select
                  name="category_id"
                  className="ui-field__select"
                  value={form.category_id}
                  onChange={updateField}
                >
                  <option value="">Select a category…</option>
                  {categories.map((c) => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </select>
                {fieldError('category_id') && (
                  <p className="ui-field__error">{fieldError('category_id')}</p>
                )}
              </label>

              <label className="ui-field">
                <span className="ui-field__label">Location</span>
                <input
                  type="text"
                  name="location"
                  className="ui-field__input"
                  placeholder="e.g. Ntinda, Kampala"
                  value={form.location}
                  onChange={updateField}
                />
                {fieldError('location') && (
                  <p className="ui-field__error">{fieldError('location')}</p>
                )}
              </label>

              <label className="ui-field">
                <span className="ui-field__label">About your service</span>
                <textarea
                  name="bio"
                  className="ui-field__textarea"
                  rows={5}
                  placeholder="Describe what you offer, who you typically work with, and what makes you different."
                  value={form.bio}
                  onChange={updateField}
                />
                {fieldError('bio') && (
                  <p className="ui-field__error">{fieldError('bio')}</p>
                )}
              </label>
            </section>

            <section className="edit__section">
              <h2 className="edit__section-title">Contact & availability</h2>

              <label className="ui-field">
                <span className="ui-field__label">WhatsApp number</span>
                <input
                  type="text"
                  name="whatsapp_number"
                  className="ui-field__input"
                  placeholder="256701234567"
                  value={form.whatsapp_number}
                  onChange={updateField}
                />
                <p className="ui-field__hint">
                  Digits only, including country code. Example: 256701234567
                </p>
                {fieldError('whatsapp_number') && (
                  <p className="ui-field__error">{fieldError('whatsapp_number')}</p>
                )}
              </label>

              <label className="ui-field">
                <span className="ui-field__label">Availability</span>
                <select
                  name="availability_status"
                  className="ui-field__select"
                  value={form.availability_status}
                  onChange={updateField}
                >
                  <option value="available">Available</option>
                  <option value="busy">Busy</option>
                  <option value="unavailable">Unavailable</option>
                </select>
              </label>
            </section>

            <section className="edit__section">
              <h2 className="edit__section-title"> Booking Pricing</h2>
              <p className="edit__section-hint">
                Optional.
                This is just Booking price not the actual price of your service. You can always discuss the actual price with the client after they reach out to you on whatsapp. If you don't want to show a booking price range, leave these fields blank.
              </p>

              <div className="edit__grid-2">
                <label className="ui-field">
                  <span className="ui-field__label">Minimum (UGX)</span>
                  <input
                    type="number"
                    name="price_range_min"
                    className="ui-field__input"
                    placeholder="500000"
                    value={form.price_range_min}
                    onChange={updateField}
                  />
                  {fieldError('price_range_min') && (
                    <p className="ui-field__error">{fieldError('price_range_min')}</p>
                  )}
                </label>
 
                <label className="ui-field">
                  <span className="ui-field__label">Maximum (UGX)</span>
                  <input
                    type="number"
                    name="price_range_max"
                    className="ui-field__input"
                    placeholder="5000000"
                    value={form.price_range_max}
                    onChange={updateField}
                  />
                  {fieldError('price_range_max') && (
                    <p className="ui-field__error">{fieldError('price_range_max')}</p>
                  )}
                </label>
              </div>
            </section>

            <section className="edit__section">
              <h2 className="edit__section-title">Photos</h2>

              <div className="edit__grid-2">
                <div className="edit__upload">
                  <span className="ui-field__label">Profile photo</span>
                  <div className="edit__upload-preview edit__upload-preview--round">
                    {photoFile ? (
                      <img src={URL.createObjectURL(photoFile)} alt="New profile" />
                    ) : provider.profile_photo_path ? (
                      <img
                        src={`${STORAGE_BASE}/storage/${provider.profile_photo_path}`}
                        alt="Current profile"
                      />
                    ) : (
                      <span className="edit__upload-placeholder">
                        {form.business_name?.[0] ?? '?'}
                      </span>
                    )}
                  </div>
                  <label className="edit__upload-btn">
                    {photoFile ? 'Change' : 'Choose photo'}
                    <input
                      type="file"
                      accept="image/png,image/jpeg,image/webp"
                      onChange={(e) => setPhotoFile(e.target.files[0])}
                      hidden
                    />
                  </label>
                  {fieldError('profile_photo') && (
                    <p className="ui-field__error">{fieldError('profile_photo')}</p>
                  )}
                </div>

                <div className="edit__upload">
                  <span className="ui-field__label">Banner</span>
                  <div className="edit__upload-preview edit__upload-preview--wide">
                    {bannerFile ? (
                      <img src={URL.createObjectURL(bannerFile)} alt="New banner" />
                    ) : provider.banner_path ? (
                      <img
                        src={`${STORAGE_BASE}/storage/${provider.banner_path}`}
                        alt="Current banner"
                      />
                    ) : (
                      <span className="edit__upload-placeholder">No banner yet</span>
                    )}
                  </div>
                  <label className="edit__upload-btn">
                    {bannerFile ? 'Change' : 'Choose banner'}
                    <input
                      type="file"
                      accept="image/png,image/jpeg,image/webp"
                      onChange={(e) => setBannerFile(e.target.files[0])}
                      hidden
                    />
                  </label>
                  {fieldError('banner') && (
                    <p className="ui-field__error">{fieldError('banner')}</p>
                  )}
                </div>
              </div>
            </section>

            <div className="edit__submit-row">
              <button type="submit" className="ui-btn ui-btn--primary" disabled={saving}>
                {saving ? 'Saving…' : 'Save changes'}
              </button>
            </div>
          </form>

          <PortfolioManager
            providerId={provider.id}
            items={portfolioItems}
            onChange={setPortfolioItems}
          />
        </div>
      </main>
    </>
  );
}