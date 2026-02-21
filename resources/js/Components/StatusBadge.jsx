const statusConfig = {
    paid: { label: 'Paid', bg: 'bg-green-100', text: 'text-green-700', dot: 'bg-green-500' },
    completed: { label: 'Completed', bg: 'bg-emerald-100', text: 'text-emerald-700', dot: 'bg-emerald-500' },
    pending: { label: 'Pending', bg: 'bg-amber-100', text: 'text-amber-700', dot: 'bg-amber-500' },
    failed: { label: 'Failed', bg: 'bg-red-100', text: 'text-red-700', dot: 'bg-red-500' },
    refunded: { label: 'Refunded', bg: 'bg-blue-100', text: 'text-blue-700', dot: 'bg-blue-500' },
};

export default function StatusBadge({ status, className = '' }) {
    const key = status?.toLowerCase() || 'pending';
    const config = statusConfig[key] || statusConfig.pending;

    return (
        <span className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold ${config.bg} ${config.text} ${className}`}>
            <span className={`w-1.5 h-1.5 rounded-full ${config.dot}`} />
            {config.label}
        </span>
    );
}
