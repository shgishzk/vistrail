import axios from 'axios';

export const requestVisitReassignment = async (visitId) => {
  const { data } = await axios.patch(`/api/visits/${visitId}/request-reassignment`);
  return data;
};

export const returnVisitAsUnstarted = async (visitId) => {
  const { data } = await axios.patch(`/api/visits/${visitId}/return-unstarted`);
  return data;
};
