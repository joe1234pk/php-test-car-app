function ActionBar({ syncingCars, syncingQuotes, loadingCars, onSyncCars, onSyncQuotes, onRefreshCars }) {
  return (
    <div className="mt-5 flex flex-wrap gap-3">
      <button
        onClick={onSyncCars}
        disabled={syncingCars}
        className="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
      >
        {syncingCars ? 'Syncing cars...' : 'Sync Cars'}
      </button>
      {onSyncQuotes && (
        <button
          onClick={onSyncQuotes}
          disabled={syncingQuotes}
          className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
        >
          {syncingQuotes ? 'Syncing quotes...' : 'Sync Quotes'}
        </button>
      )}
      <button
        onClick={onRefreshCars}
        disabled={loadingCars}
        className="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 disabled:opacity-50"
      >
        {loadingCars ? 'Loading cars...' : 'Refresh Cars'}
      </button>
    </div>
  )
}

export default ActionBar
