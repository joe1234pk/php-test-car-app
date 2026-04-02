import { Link, useParams } from 'react-router-dom'
import { useCallback, useEffect, useMemo, useState } from 'react'
import ErrorBanner from '../components/ErrorBanner.jsx'
import QuotePanel from '../components/QuotePanel.jsx'
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

  const syncQuotesForCar = async () => {
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
  }

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

        <div className="mt-5 flex flex-wrap gap-3">
          <button
            onClick={syncQuotesForCar}
            disabled={syncingQuotes}
            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
          >
            {syncingQuotes ? 'Syncing from live API...' : 'Sync Quotes from Live API'}
          </button>
          <button
            onClick={loadQuotes}
            disabled={loadingQuotes}
            className="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 disabled:opacity-50"
          >
            <svg
              aria-hidden="true"
              viewBox="0 0 20 20"
              fill="none"
              className="h-4 w-4"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M16 10a6 6 0 1 1-1.757-4.243"
                stroke="currentColor"
                strokeWidth="1.8"
                strokeLinecap="round"
              />
              <path
                d="M16 4v3.5h-3.5"
                stroke="currentColor"
                strokeWidth="1.8"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            </svg>
            {loadingQuotes ? 'Reloading quotes...' : 'Reload Quotes'}
          </button>
        </div>
        <p className="mt-2 text-xs text-slate-500">
          Sync pulls latest quotes from live API. Reload fetches currently stored quotes from this app.
        </p>

        <ErrorBanner message={error} />
      </div>

      <QuotePanel selectedCar={selectedCar} loadingQuotes={loadingQuotes} quotes={quotes} />
    </main>
  )
}

export default CarQuotesPage
