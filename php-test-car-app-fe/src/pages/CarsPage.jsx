import { Link } from 'react-router-dom'
import { useEffect, useState } from 'react'
import ActionBar from '../components/ActionBar.jsx'
import ErrorBanner from '../components/ErrorBanner.jsx'
import { apiClient } from '../services/apiClient.js'

function CarsPage() {
  const [cars, setCars] = useState([])
  const [loadingCars, setLoadingCars] = useState(false)
  const [syncingCars, setSyncingCars] = useState(false)
  const [error, setError] = useState('')

  const loadCars = async () => {
    setError('')
    setLoadingCars(true)
    try {
      const nextCars = await apiClient.getCars()
      setCars(nextCars)
    } catch (requestError) {
      setError(requestError.message)
    } finally {
      setLoadingCars(false)
    }
  }

  const syncCars = async () => {
    setError('')
    setSyncingCars(true)
    try {
      await apiClient.syncCars()
      await loadCars()
    } catch (requestError) {
      setError(requestError.message)
    } finally {
      setSyncingCars(false)
    }
  }

  useEffect(() => {
    loadCars()
  }, [])

  return (
    <main className="mx-auto max-w-6xl p-6 md:p-10">
      <div className="mb-8 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h1 className="mb-2 text-2xl font-semibold text-slate-900">PHP Test Car App</h1>
        <p className="text-sm text-slate-600">Car operations and list</p>

        <ActionBar
          syncingCars={syncingCars}
          loadingCars={loadingCars}
          onSyncCars={syncCars}
          onRefreshCars={loadCars}
        />

        <ErrorBanner message={error} />
      </div>

      <section className="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 className="mb-4 text-lg font-semibold text-slate-900">Cars</h2>
        <div className="max-h-[34rem] overflow-auto rounded-md border border-slate-200">
          <table className="min-w-full text-left text-sm">
            <thead className="bg-slate-50 text-slate-600">
              <tr>
                <th className="px-3 py-2">Plate</th>
                <th className="px-3 py-2">State</th>
                <th className="px-3 py-2">Make / Model</th>
                <th className="px-3 py-2">Action</th>
              </tr>
            </thead>
            <tbody>
              {cars.length === 0 ? (
                <tr>
                  <td className="px-3 py-3 text-slate-500" colSpan={4}>
                    {loadingCars ? 'Loading cars...' : 'No cars loaded yet.'}
                  </td>
                </tr>
              ) : (
                cars.map((car) => (
                  <tr key={car.id} className="border-t border-slate-100">
                    <td className="px-3 py-2">{car.license_plate}</td>
                    <td className="px-3 py-2">{car.license_state}</td>
                    <td className="px-3 py-2">
                      {car.make} / {car.model}
                    </td>
                    <td className="px-3 py-2">
                      <Link
                        to={`/cars/${car.id}`}
                        className="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                      >
                        View quotes
                      </Link>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </section>
    </main>
  )
}

export default CarsPage
