function CarPanel({ cars, selectedCarId, onSelectCar }) {
  return (
    <div className="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
      <h2 className="mb-4 text-lg font-semibold text-slate-900">Cars</h2>
      <div className="mb-4">
        <label htmlFor="carId" className="mb-1 block text-sm text-slate-600">
          Select car to view quotes
        </label>
        <select
          id="carId"
          value={selectedCarId}
          onChange={onSelectCar}
          className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
        >
          <option value="">-- Select --</option>
          {cars.map((car) => (
            <option key={car.id} value={car.id}>
              {car.license_plate} ({car.license_state})
            </option>
          ))}
        </select>
      </div>

      <div className="max-h-72 overflow-auto rounded-md border border-slate-200">
        <table className="min-w-full text-left text-sm">
          <thead className="bg-slate-50 text-slate-600">
            <tr>
              <th className="px-3 py-2">Plate</th>
              <th className="px-3 py-2">State</th>
              <th className="px-3 py-2">Make / Model</th>
            </tr>
          </thead>
          <tbody>
            {cars.length === 0 ? (
              <tr>
                <td className="px-3 py-3 text-slate-500" colSpan={3}>
                  No cars loaded yet.
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
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}

export default CarPanel
