import axios from 'axios';

export const fetchMyAreas = async () => {
  const { data } = await axios.get('/api/areas/my');
  return data;
};
