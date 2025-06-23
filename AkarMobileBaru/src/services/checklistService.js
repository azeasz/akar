import api from '../utils/api';

export const getMyChecklists = async () => {
  try {
    const response = await api.get('/checklists/my');
    return response.data;
  } catch (error) {
    throw error.response?.data || { message: 'Terjadi kesalahan saat mengambil data checklist' };
  }
};

export const getChecklistDetail = async (id) => {
  try {
    const response = await api.get(`/checklists/${id}`);
    return response.data;
  } catch (error) {
    throw error.response?.data || { message: 'Terjadi kesalahan saat mengambil detail checklist' };
  }
};

export const createChecklist = async (checklistData) => {
  try {
    const response = await api.post('/checklists', checklistData);
    return response.data;
  } catch (error) {
    throw error.response?.data || { message: 'Terjadi kesalahan saat membuat checklist' };
  }
};

export const updateChecklist = async (id, checklistData) => {
  try {
    const response = await api.put(`/checklists/${id}`, checklistData);
    return response.data;
  } catch (error) {
    throw error.response?.data || { message: 'Terjadi kesalahan saat memperbarui checklist' };
  }
};

export const deleteChecklist = async (id) => {
  try {
    const response = await api.delete(`/checklists/${id}`);
    return response.data;
  } catch (error) {
    throw error.response?.data || { message: 'Terjadi kesalahan saat menghapus checklist' };
  }
};

export const completeChecklist = async (id) => {
  try {
    const response = await api.put(`/checklists/${id}/complete`);
    return response.data;
  } catch (error) {
    throw error.response?.data || { message: 'Terjadi kesalahan saat menyelesaikan checklist' };
  }
};

export const addFaunaToChecklist = async (checklistId, faunaData) => {
  try {
    const response = await api.post(`/checklists/${checklistId}/faunas`, faunaData);
    return response.data;
  } catch (error) {
    throw error.response?.data || { message: 'Terjadi kesalahan saat menambahkan fauna' };
  }
};

export const updateFauna = async (checklistId, faunaId, faunaData) => {
  try {
    const response = await api.put(`/checklists/${checklistId}/faunas/${faunaId}`, faunaData);
    return response.data;
  } catch (error) {
    throw error.response?.data || { message: 'Terjadi kesalahan saat memperbarui fauna' };
  }
};

export const deleteFauna = async (checklistId, faunaId) => {
  try {
    const response = await api.delete(`/checklists/${checklistId}/faunas/${faunaId}`);
    return response.data;
  } catch (error) {
    throw error.response?.data || { message: 'Terjadi kesalahan saat menghapus fauna' };
  }
}; 