import { usePage } from '@inertiajs/react';

export default function CurrencySymbol() {
    const { currency } = usePage().props;

    const symbols = {
        'INR': '₹',
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
    };

    return <>{symbols[currency] || currency}</>;
}
