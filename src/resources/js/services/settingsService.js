import axios from 'axios';

const caches = {
  private: null,
  public: null,
};

const pending = {
  private: null,
  public: null,
};

export const fetchSettings = async ({ isPublic = false, force = false } = {}) => {
  const key = isPublic ? 'public' : 'private';

  if (!force) {
    if (caches[key]) {
      return caches[key];
    }
    if (pending[key]) {
      return pending[key];
    }
  }

  const endpoint = isPublic ? '/api/settings/public' : '/api/settings';

  pending[key] = axios
    .get(endpoint)
    .then(({ data }) => {
      const settings = data?.settings || {};
      caches[key] = settings;
      pending[key] = null;
      return settings;
    })
    .catch((error) => {
      pending[key] = null;
      throw error;
    });

  return pending[key];
};

export const updateSettings = async (settings) => {
  const { data } = await axios.put('/api/settings', { settings });
  const resolved = data?.settings || {};
  caches.private = resolved;
  caches.public = null;
  return resolved;
};

export const clearSettingsCache = ({ includePublic = true } = {}) => {
  caches.private = null;
  if (includePublic) {
    caches.public = null;
  }
};
