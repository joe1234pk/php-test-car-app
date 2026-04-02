function RefreshIcon() {
  return (
    <svg
      aria-hidden="true"
      viewBox="0 0 20 20"
      fill="none"
      className="h-4 w-4"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M16 10a6 6 0 1 1-1.757-4.243" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
      <path
        d="M16 4v3.5h-3.5"
        stroke="currentColor"
        strokeWidth="1.8"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  )
}

function ActionBar({ actions = [] }) {
  const variantClassMap = {
    primary: 'rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50',
    secondary: 'rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50',
    outline:
      'inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 disabled:opacity-50',
  }

  return (
    <div className="mt-5 flex flex-wrap gap-3">
      {actions.map((action) => (
        <button
          key={action.id}
          onClick={action.onClick}
          disabled={Boolean(action.loading || action.disabled)}
          className={variantClassMap[action.variant ?? 'primary']}
        >
          {action.icon === 'refresh' && <RefreshIcon />}
          {action.loading ? action.loadingLabel ?? action.label : action.label}
        </button>
      ))}
    </div>
  )
}

export default ActionBar
