import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import CarQuotesPage from '../pages/CarQuotesPage.jsx'
import CarsPage from '../pages/CarsPage.jsx'

function AppRouter() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Navigate to="/cars" replace />} />
        <Route path="/cars" element={<CarsPage />} />
        <Route path="/cars/:carId" element={<CarQuotesPage />} />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  )
}

export default AppRouter
