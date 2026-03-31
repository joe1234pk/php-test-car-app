function QuotePanel({ selectedCar, loadingQuotes, quotes }) {
  return (
    <div className="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
      <h2 className="mb-2 text-lg font-semibold text-slate-900">Quotes</h2>
      <p className="mb-4 text-sm text-slate-600">
        {selectedCar
          ? `Showing quotes for ${selectedCar.license_plate} (${selectedCar.license_state})`
          : 'Select a car to view quotes.'}
      </p>

      <div className="max-h-72 overflow-auto rounded-md border border-slate-200">
        <table className="min-w-full text-left text-sm">
          <thead className="bg-slate-50 text-slate-600">
            <tr>
              <th className="px-3 py-2">Repairer</th>
              <th className="px-3 py-2">Price</th>
              <th className="px-3 py-2">Overview</th>
            </tr>
          </thead>
          <tbody>
            {loadingQuotes ? (
              <tr>
                <td className="px-3 py-3 text-slate-500" colSpan={3}>
                  Loading quotes...
                </td>
              </tr>
            ) : quotes.length === 0 ? (
              <tr>
                <td className="px-3 py-3 text-slate-500" colSpan={3}>
                  No quotes loaded.
                </td>
              </tr>
            ) : (
              quotes.map((quote) => (
                <tr key={quote.id} className="border-t border-slate-100">
                  <td className="px-3 py-2">{quote.repairer}</td>
                  <td className="px-3 py-2">${Number(quote.price).toFixed(2)}</td>
                  <td className="px-3 py-2">{quote.overview_of_work}</td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}

export default QuotePanel
