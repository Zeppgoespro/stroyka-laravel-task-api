import axios from 'axios';

export const useSliders = async () => {
  const { data } = await axios.get('http://localhost/api/sliders');
  return data;
};
