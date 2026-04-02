import { Link, useParams } from 'react-router-dom'
import { useCallback, useEffect, useMemo, useState } from 'react'
import ActionBar from '../components/ActionBar.jsx'
import DataTable from '../components/DataTable.jsx'
import ErrorBanner from '../components/ErrorBanner.jsx'
import { apiClient } from '../services/apiClient.js'

function CarQuotesPage() {
  const { carId } = useParams()
  const [cars, setCars] = useState([])
  const [quotes, setQuotes] = useState([])
  const [loadingQuotes, setLoadingQuotes] = useState(false)
  const [syncingQuotes, setSyncingQuotes] = useState(false)
  const [error, setError] = useState('')

  const selectedCar = useMemo(
    () => cars.find((car) => String(car.id) === String(carId)) ?? null,
    [cars, carId]
  )

  const loadCars = useCallback(async () => {
    const nextCars = await apiClient.getCars()
    setCars(nextCars)
  }, [])

  const loadQuotes = useCallback(async () => {
    if (!carId) return
    setError('')
    setLoadingQuotes(true)
    try {
      const nextQuotes = await apiClient.getQuotesByCarId(carId)
      setQuotes(nextQuotes)
    } catch (requestError) {
      setError(requestError.message)
      setQuotes([])
    } finally {
      setLoadingQuotes(false)
    }
  }, [carId])

  const syncQuotesForCar = useCallback(async () => {
    if (!carId) return
    setError('')
    setSyncingQuotes(true)
    try {
      await apiClient.syncQuotesByCarId(carId)
      await loadQuotes()
    } catch (requestError) {
      setError(requestError.message)
    } finally {
      setSyncingQuotes(false)
    }
  }, [carId, loadQuotes])

  useEffect(() => {
    const loadPageData = async () => {
      setError('')
      try {
        await loadCars()
        await loadQuotes()
      } catch (requestError) {
        setError(requestError.message)
      }
    }

    loadPageData()
  }, [loadCars, loadQuotes])

  const actions = useMemo(
    () => [
      {
        id: 'sync-quotes',
        label: 'Sync Quotes from Live API',
        loadingLabel: 'Syncing from live API...',
        loading: syncingQuotes,
        onClick: syncQuotesForCar,
        variant: 'secondary',
      },
      {
        id: 'reload-quotes',
        label: 'Reload Saved Quotes',
        loadingLabel: 'Reloading saved quotes...',
        loading: loadingQuotes,
        onClick: loadQuotes,
        variant: 'outline',
        icon: 'refresh',
      },
    ],
    [syncingQuotes, syncQuotesForCar, loadingQuotes, loadQuotes]
  )

  const quoteColumns = useMemo(
    () => [
      { key: 'repairer', header: 'Repairer' },
      {
        key: 'price',
        header: 'Price',
        renderCell: (quote) => `$${Number(quote.price).toFixed(2)}`,
      },
      { key: 'overview_of_work', header: 'Overview' },
    ],
    []
  )

  return (
    <main className="mx-auto max-w-6xl p-6 md:p-10">
      <div className="mb-8 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div className="mb-4 flex items-center justify-between">
          <h1 className="text-2xl font-semibold text-slate-900">Car Quotes</h1>
          <Link to="/cars" className="text-sm font-medium text-indigo-600 hover:text-indigo-500">
            Back to cars
          </Link>
        </div>

        <p className="text-sm text-slate-600">
          {selectedCar
            ? `Car: ${selectedCar.license_plate} (${selectedCar.license_state})`
            : `Car ID: ${carId}`}
        </p>

        <ActionBar actions={actions} />
        <p className="mt-2 text-xs text-slate-500">
          Sync pulls latest quotes from live API. Reload fetches currently stored quotes from this app.
        </p>

        <ErrorBanner message={error} />
      </div>

      <section className="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 className="mb-2 text-lg font-semibold text-slate-900">Quotes</h2>
        <p className="mb-4 text-sm text-slate-600">
          {selectedCar
            ? `Showing quotes for ${selectedCar.license_plate} (${selectedCar.license_state})`
            : 'Select a car to view quotes.'}
        </p>
        <DataTable
          columns={quoteColumns}
          rows={quotes}
          rowKey={(quote) => quote.id}
          isLoading={loadingQuotes}
          loadingText="Loading quotes..."
          emptyText="No quotes loaded."
        />
      </section>
    </main>
  )
}

export default CarQuotesPage
