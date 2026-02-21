export default function SkeletonLoader({ variant = 'card', count = 1, className = '' }) {
    const items = Array.from({ length: count }, (_, i) => i);

    if (variant === 'stat-card') {
        return (
            <div className={`grid grid-cols-2 md:grid-cols-5 gap-4 ${className}`}>
                {items.map((i) => (
                    <div key={i} className="rounded-2xl p-4 bg-gray-100 border-2 border-dashed border-gray-200 animate-pulse">
                        <div className="w-8 h-8 bg-gray-200 rounded-full mx-auto mb-2" />
                        <div className="h-6 bg-gray-200 rounded-lg w-16 mx-auto mb-2" />
                        <div className="h-3 bg-gray-200 rounded w-20 mx-auto" />
                    </div>
                ))}
            </div>
        );
    }

    if (variant === 'table-row') {
        return (
            <div className={`space-y-3 ${className}`}>
                {items.map((i) => (
                    <div key={i} className="flex items-center gap-4 p-4 bg-gray-50 rounded-xl animate-pulse">
                        <div className="w-10 h-10 bg-gray-200 rounded-full flex-shrink-0" />
                        <div className="flex-1 space-y-2">
                            <div className="h-4 bg-gray-200 rounded w-1/3" />
                            <div className="h-3 bg-gray-200 rounded w-1/2" />
                        </div>
                        <div className="h-6 bg-gray-200 rounded-full w-16" />
                    </div>
                ))}
            </div>
        );
    }

    // Default: card variant
    return (
        <div className={`grid grid-cols-1 md:grid-cols-3 gap-6 ${className}`}>
            {items.map((i) => (
                <div key={i} className="rounded-2xl border-2 border-dashed border-gray-200 p-6 animate-pulse">
                    <div className="h-4 bg-gray-200 rounded w-1/3 mb-3" />
                    <div className="h-6 bg-gray-200 rounded w-2/3 mb-4" />
                    <div className="space-y-2">
                        <div className="h-3 bg-gray-200 rounded w-full" />
                        <div className="h-3 bg-gray-200 rounded w-4/5" />
                    </div>
                    <div className="mt-4 h-10 bg-gray-200 rounded-full" />
                </div>
            ))}
        </div>
    );
}
