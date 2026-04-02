const request = async (path, options = {}) => {
  const response = await fetch(path, options)
  const data = await response.json().catch(() => ({}))

  if (!response.ok) {
    throw new Error(data.message || `Request failed: ${response.status}`)
  }

  return data
}

export const apiClient = {
  getCars: async () => {
    const response = await request('/api/cars')
    return response.data || []
  },
  syncCars: async () => request('/api/sync/cars', { method: 'POST' }),
  syncQuotes: async () => request('/api/sync/quotes', { method: 'POST' }),
  syncQuotesByCarId: async (carId) => request(`/api/sync/quotes/${carId}`, { method: 'POST' }),
  getQuotesByCarId: async (carId) => {
    const response = await request(`/api/cars/${carId}/quotes`)
    return response.data || []
  },
}
