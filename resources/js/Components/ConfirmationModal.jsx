import { useState, useEffect } from 'react';
import CurrencySymbol from '@/Components/CurrencySymbol';

export default function ConfirmationModal({ show, onClose, title = 'Payment Successful!', message, details = [], stampsEarned = 0 }) {
    const [animate, setAnimate] = useState(false);

    useEffect(() => {
        if (show) {
            const timer = setTimeout(() => setAnimate(true), 100);
            return () => clearTimeout(timer);
        }
        setAnimate(false);
    }, [show]);

    if (!show) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" onClick={onClose}>
            <div
                className={`bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center transform transition-all duration-500 ${animate ? 'scale-100 opacity-100' : 'scale-90 opacity-0'}`}
                onClick={(e) => e.stopPropagation()}
            >
                {/* Success checkmark */}
                <div className={`mx-auto w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mb-5 transition-all duration-700 ${animate ? 'scale-100' : 'scale-0'}`}>
                    <svg className={`w-10 h-10 text-green-500 transition-all duration-500 delay-300 ${animate ? 'opacity-100' : 'opacity-0'}`} fill="none" viewBox="0 0 24 24" strokeWidth="3" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>

                <h3 className="font-display text-xl text-gray-900 mb-1">{title}</h3>
                {message && <p className="text-sm text-gray-500 mb-4">{message}</p>}

                {/* Details */}
                {details.length > 0 && (
                    <div className="bg-gray-50 rounded-xl p-4 mb-4 text-left space-y-2">
                        {details.map((detail, i) => (
                            <div key={i} className="flex justify-between text-sm">
                                <span className="text-gray-500">{detail.label}</span>
                                <span className="font-bold text-gray-900">{detail.value}</span>
                            </div>
                        ))}
                    </div>
                )}

                {/* Stamps earned */}
                {stampsEarned > 0 && (
                    <div className={`bg-lucky-50 border-2 border-dashed border-lucky-200 rounded-xl p-4 mb-5 transition-all duration-700 delay-500 ${animate ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'}`}>
                        <div className="flex items-center justify-center gap-2">
                            <span className="text-2xl">🎫</span>
                            <span className="font-display text-lg text-lucky-700">+{stampsEarned} Stamps Earned!</span>
                        </div>
                    </div>
                )}

                <button
                    onClick={onClose}
                    className="w-full lucky-gradient text-white font-bold py-3 px-6 rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all text-sm"
                >
                    Done
                </button>
            </div>
        </div>
    );
}
