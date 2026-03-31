import { useEffect, useMemo, useState } from 'react'
import ActionBar from '../components/ActionBar.jsx'
import CarPanel from '../components/CarPanel.jsx'
import ErrorBanner from '../components/ErrorBanner.jsx'
import QuotePanel from '../components/QuotePanel.jsx'
import { apiClient } from '../services/apiClient.js'

function HomePage() {
  const [cars, setCars] = useState([])
  const [quotes, setQuotes] = useState([])
  const [selectedCarId, setSelectedCarId] = useState('')
  const [loadingCars, setLoadingCars] = useState(false)
  const [loadingQuotes, setLoadingQuotes] = useState(false)
  const [syncingCars, setSyncingCars] = useState(false)
  const [syncingQuotes, setSyncingQuotes] = useState(false)
  const [error, setError] = useState('')

  const selectedCar = useMemo(
    () => cars.find((car) => String(car.id) === String(selectedCarId)) ?? null,
    [cars, selectedCarId]
  )

  const loadCars = async () => {
    setError('')
    setLoadingCars(true)
    try {
      const nextCars = await apiClient.getCars()
      setCars(nextCars)
      if (nextCars.length > 0 && selectedCarId === '') setSelectedCarId(String(nextCars[0].id))
    } catch (requestError) {
      setError(requestError.message)
    } finally {
      setLoadingCars(false)
    }
  }

  const loadQuotes = async (carId) => {
    if (!carId) {
      setQuotes([])
      return
    }

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

  const syncQuotes = async () => {
    setError('')
    setSyncingQuotes(true)
    try {
      await apiClient.syncQuotes()
      if (selectedCarId !== '') await loadQuotes(selectedCarId)
    } catch (requestError) {
      setError(requestError.message)
    } finally {
      setSyncingQuotes(false)
    }
  }

  const onSelectCar = async (event) => {
    const carId = event.target.value
    setSelectedCarId(carId)
    await loadQuotes(carId)
  }

  useEffect(() => {
    loadCars()
  }, [])

  return (
    <main className="mx-auto max-w-6xl p-6 md:p-10">
      <div className="mb-8 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h1 className="mb-2 text-2xl font-semibold text-slate-900">PHP Test Car App FE</h1>
        <p className="text-sm text-slate-600">
          React + Tailwind UI for syncing and viewing cars / quotes from backend API.
        </p>

        <ActionBar
          syncingCars={syncingCars}
          syncingQuotes={syncingQuotes}
          loadingCars={loadingCars}
          onSyncCars={syncCars}
          onSyncQuotes={syncQuotes}
          onRefreshCars={loadCars}
        />

        <ErrorBanner message={error} />
      </div>

      <section className="grid gap-6 md:grid-cols-2">
        <CarPanel cars={cars} selectedCarId={selectedCarId} onSelectCar={onSelectCar} />
        <QuotePanel selectedCar={selectedCar} loadingQuotes={loadingQuotes} quotes={quotes} />
      </section>
    </main>
  )
}

export default HomePage
