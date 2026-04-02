import { Link } from 'react-router-dom'
import { useCallback, useEffect, useMemo, useState } from 'react'
import ActionBar from '../components/ActionBar.jsx'
import DataTable from '../components/DataTable.jsx'
import ErrorBanner from '../components/ErrorBanner.jsx'
import { apiClient } from '../services/apiClient.js'

function CarsPage() {
  const [cars, setCars] = useState([])
  const [loadingCars, setLoadingCars] = useState(false)
  const [syncingCars, setSyncingCars] = useState(false)
  const [error, setError] = useState('')

  const loadCars = useCallback(async () => {
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
  }, [])

  const syncCars = useCallback(async () => {
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
  }, [loadCars])

  useEffect(() => {
    loadCars()
  }, [loadCars])

  const actions = useMemo(
    () => [
      {
        id: 'sync-cars',
        label: 'Sync Cars from Live API',
        loadingLabel: 'Syncing cars from live API...',
        loading: syncingCars,
        onClick: syncCars,
        variant: 'primary',
      },
      {
        id: 'reload-cars',
        label: 'Reload Saved Cars',
        loadingLabel: 'Reloading saved cars...',
        loading: loadingCars,
        onClick: loadCars,
        variant: 'outline',
        icon: 'refresh',
      },
    ],
    [syncingCars, syncCars, loadingCars, loadCars]
  )

  const columns = useMemo(
    () => [
      { key: 'plate', header: 'Plate', renderCell: (car) => car.license_plate },
      { key: 'state', header: 'State', renderCell: (car) => car.license_state },
      { key: 'makeModel', header: 'Make / Model', renderCell: (car) => `${car.make} / ${car.model}` },
      {
        key: 'action',
        header: 'Action',
        renderCell: (car) => (
          <Link to={`/cars/${car.id}`} className="text-sm font-medium text-indigo-600 hover:text-indigo-500">
            View quotes
          </Link>
        ),
      },
    ],
    []
  )

  return (
    <main className="mx-auto max-w-6xl p-6 md:p-10">
      <div className="mb-8 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h1 className="mb-2 text-2xl font-semibold text-slate-900">PHP Test Car App</h1>
        <p className="text-sm text-slate-600">Car operations and list</p>

        <ActionBar actions={actions} />
        <p className="mt-2 text-xs text-slate-500">
          Sync pulls latest car list from live API. Reload fetches currently stored cars from this app.
        </p>

        <ErrorBanner message={error} />
      </div>

      <section className="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 className="mb-4 text-lg font-semibold text-slate-900">Cars</h2>
        <DataTable
          columns={columns}
          rows={cars}
          rowKey={(car) => car.id}
          isLoading={loadingCars}
          loadingText="Loading cars..."
          emptyText="No cars loaded yet."
          maxHeightClass="max-h-[34rem]"
        />
      </section>
    </main>
  )
}

export default CarsPage
