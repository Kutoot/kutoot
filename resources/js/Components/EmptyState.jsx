import { Link } from '@inertiajs/react';

export default function EmptyState({ icon = '📭', title, description, actionLabel, actionHref, className = '' }) {
    return (
        <div className={`flex flex-col items-center justify-center py-12 px-4 ${className}`}>
            <div className="w-20 h-20 rounded-full bg-gradient-to-br from-lucky-50 to-lucky-100 flex items-center justify-center mb-4 animate-pulse-glow">
                <span className="text-4xl">{icon}</span>
            </div>
            <h3 className="font-display text-lg text-gray-900 mb-1">{title}</h3>
            {description && (
                <p className="text-sm text-gray-500 text-center max-w-sm mb-4">{description}</p>
            )}
            {actionLabel && actionHref && (
                <Link
                    href={actionHref}
                    className="inline-flex items-center gap-2 px-5 py-2.5 lucky-gradient text-white font-bold text-sm rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all"
                >
                    {actionLabel}
                </Link>
            )}
        </div>
    );
}
