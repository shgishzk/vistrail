import axios from 'axios';

export const requestVisitReassignment = async (visitId) => {
  const { data } = await axios.patch(`/api/visits/${visitId}/request-reassignment`);
  return data;
};

export const returnVisitAsUnstarted = async (visitId) => {
  const { data } = await axios.patch(`/api/visits/${visitId}/return-unstarted`);
  return data;
};

export const completeVisit = async (visitId) => {
  const { data } = await axios.patch(`/api/visits/${visitId}/complete`);
  return data;
};

export const fetchReassignableVisits = async () => {
  const { data } = await axios.get('/api/visits/pending-reassignment');
  return data;
};

export const acceptReassignment = async (visitId) => {
  const { data } = await axios.post(`/api/visits/${visitId}/accept-reassignment`);
  return data;
};
