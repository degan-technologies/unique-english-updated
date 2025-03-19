// resources/js/utils/urlHelper.js

export const BASE_URL = "http://127.0.0.1:8000/storage/";

// Helper function to create a full URL from a relative path
export const fullUrl = (path, fallback) => {
  if (!path) return fallback;
  if (path.startsWith("http")) return path;
  return BASE_URL + path;
};
