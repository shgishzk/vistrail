import axios from 'axios';

export const fetchMyAreas = async () => {
  const { data } = await axios.get('/api/areas/my');
  return data;
};

export const fetchAreas = async (params = {}) => {
  const { data } = await axios.get('/api/areas', { params });
  return data;
};

export const fetchAreaDetail = async (areaId) => {
  const { data } = await axios.get(`/api/areas/${areaId}`);
  return data;
};
