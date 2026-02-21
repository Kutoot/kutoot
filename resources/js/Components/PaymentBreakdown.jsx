import CurrencySymbol from '@/Components/CurrencySymbol';

export default function PaymentBreakdown({ billAmount, discount, finalBill, platformFee, gst, gstRate, total, className = '' }) {
    return (
        <div className={`bg-gradient-to-br from-lucky-50 to-ticket-50 rounded-2xl border-2 border-dashed border-lucky-200 overflow-hidden ${className}`}>
            <div className="p-4 space-y-2">
                <BreakdownRow label="Total Bill" value={billAmount} />
                <BreakdownRow label="Discount Applied" value={discount} prefix="-" valueClass="text-green-600" />
                <div className="border-t border-dashed border-lucky-200 pt-2">
                    <BreakdownRow label="Bill after Discount" value={finalBill} bold valueClass="text-lucky-700" />
                </div>
                <BreakdownRow label="Platform Fee" value={platformFee} muted />
                <BreakdownRow label={`GST (${gstRate}%)`} value={gst} muted />
            </div>
            <div className="bg-lucky-100/50 px-4 py-3 border-t-2 border-dashed border-lucky-300">
                <div className="flex justify-between items-center">
                    <span className="font-bold text-lucky-800 flex items-center gap-1.5">
                        <span className="text-lg">💰</span> Total to Pay
                    </span>
                    <span className="text-xl font-bold text-lucky-800">
                        <CurrencySymbol />{total.toFixed(2)}
                    </span>
                </div>
            </div>
        </div>
    );
}

function BreakdownRow({ label, value, prefix = '', bold = false, muted = false, valueClass = '' }) {
    return (
        <div className="flex justify-between items-center text-sm">
            <span className={muted ? 'text-gray-500' : 'text-gray-700'}>{label}</span>
            <span className={`${bold ? 'font-bold' : 'font-semibold'} ${valueClass || (muted ? 'text-gray-500' : 'text-gray-900')}`}>
                {prefix && <span>{prefix} </span>}
                <CurrencySymbol />{value.toFixed(2)}
            </span>
        </div>
    );
}
