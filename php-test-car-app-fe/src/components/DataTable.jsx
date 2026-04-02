function DataTable({ columns, rows, rowKey, isLoading, loadingText, emptyText, maxHeightClass = 'max-h-72' }) {
  const hasRows = rows.length > 0

  return (
    <div className={`${maxHeightClass} overflow-auto rounded-md border border-slate-200`}>
      <table className="min-w-full text-left text-sm">
        <thead className="bg-slate-50 text-slate-600">
          <tr>
            {columns.map((column) => (
              <th key={column.key} className={`px-3 py-2 ${column.headerClassName ?? ''}`}>
                {column.header}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {!hasRows ? (
            <tr>
              <td className="px-3 py-3 text-slate-500" colSpan={columns.length}>
                {isLoading ? loadingText : emptyText}
              </td>
            </tr>
          ) : (
            rows.map((row) => (
              <tr key={rowKey(row)} className="border-t border-slate-100">
                {columns.map((column) => (
                  <td key={`${column.key}-${rowKey(row)}`} className={`px-3 py-2 ${column.cellClassName ?? ''}`}>
                    {column.renderCell ? column.renderCell(row) : row[column.key]}
                  </td>
                ))}
              </tr>
            ))
          )}
        </tbody>
      </table>
    </div>
  )
}

export default DataTable
