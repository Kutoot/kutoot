import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import CurrencySymbol from '@/Components/CurrencySymbol';

export default function Dashboard({ auth, user, plan, primaryCampaign, stats, recentTransactions, recentRedemptions, activityLogs }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>}
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                    {/* Profile & Plan Row */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {/* Profile Card */}
                        <div className="bg-white shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">Profile</h3>
                            <dl className="space-y-2 text-sm">
                                <div className="flex justify-between">
                                    <dt className="text-gray-500">Name</dt>
                                    <dd className="font-medium text-gray-900">{user.name}</dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-gray-500">Email</dt>
                                    <dd className="font-medium text-gray-900">{user.email}</dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-gray-500">Member Since</dt>
                                    <dd className="font-medium text-gray-900">{user.created_at}</dd>
                                </div>
                                {primaryCampaign && (
                                    <div className="flex justify-between">
                                        <dt className="text-gray-500">Primary Campaign</dt>
                                        <dd className="font-medium text-indigo-600">{primaryCampaign}</dd>
                                    </div>
                                )}
                            </dl>
                        </div>

                        {/* Active Plan Card */}
                        <div className="bg-white shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">Active Plan</h3>
                            {plan ? (
                                <>
                                    <p className="text-2xl font-bold text-indigo-600 mb-3">
                                        {plan.name}
                                        {plan.is_default && <span className="ml-2 text-xs font-normal text-gray-400">(Base)</span>}
                                    </p>
                                    <div className="grid grid-cols-2 gap-3 text-sm">
                                        <div className="bg-indigo-50 rounded-lg p-3 text-center">
                                            <p className="text-xl font-bold text-indigo-600">{plan.stamps_on_purchase}</p>
                                            <p className="text-xs text-gray-500">Stamps on Purchase</p>
                                        </div>
                                        <div className="bg-indigo-50 rounded-lg p-3 text-center">
                                            <p className="text-xl font-bold text-indigo-600">{plan.stamps_per_100}</p>
                                            <p className="text-xs text-gray-500">Stamps / <CurrencySymbol />100</p>
                                        </div>
                                        <div className="bg-gray-50 rounded-lg p-3 text-center">
                                            <p className="text-xl font-bold text-gray-700">{plan.max_discounted_bills}</p>
                                            <p className="text-xs text-gray-500">Max Discounted Bills</p>
                                        </div>
                                        <div className="bg-gray-50 rounded-lg p-3 text-center">
                                            <p className="text-xl font-bold text-gray-700"><CurrencySymbol />{plan.max_redeemable_amount.toFixed(2)}</p>
                                            <p className="text-xs text-gray-500">Max Redeemable</p>
                                        </div>
                                    </div>
                                    {plan.expires_at && (
                                        <p className="text-xs text-gray-500 mt-3 text-center">
                                            Expires: <span className="font-medium text-gray-700">{plan.expires_at}</span>
                                            {plan.duration_days && <span> ({plan.duration_days} day plan)</span>}
                                        </p>
                                    )}
                                </>
                            ) : (
                                <p className="text-gray-400">No active plan</p>
                            )}
                        </div>
                    </div>

                    {/* Stats Row */}
                    <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <StatCard label="Total Stamps" value={stats.stamps_count} color="indigo" />
                        <StatCard label="Coupons Used" value={stats.total_coupons_used} color="green" />
                        <StatCard label="Discount Redeemed" value={<><CurrencySymbol />{stats.total_discount_redeemed.toFixed(2)}</>} color="emerald" />
                        <StatCard label="Bills Remaining" value={stats.remaining_bills} color="amber" />
                        <StatCard label="Redeem Amount Left" value={<><CurrencySymbol />{stats.remaining_redeem_amount.toFixed(2)}</>} color="rose" />
                    </div>

                    {/* Transactions & Redemptions */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Recent Transactions */}
                        <div className="bg-white shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">Recent Transactions</h3>
                            {recentTransactions.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full text-sm">
                                        <thead>
                                            <tr className="border-b text-left text-gray-500">
                                                <th className="pb-2 font-medium">Coupon</th>
                                                <th className="pb-2 font-medium">Location</th>
                                                <th className="pb-2 font-medium text-right">Amount</th>
                                                <th className="pb-2 font-medium text-right">When</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y">
                                            {recentTransactions.map(t => (
                                                <tr key={t.id}>
                                                    <td className="py-2 text-gray-900">{t.coupon_title ?? '—'}</td>
                                                    <td className="py-2 text-gray-600">{t.location_name ?? '—'}</td>
                                                    <td className="py-2 text-right font-medium"><CurrencySymbol />{t.total_amount.toFixed(2)}</td>
                                                    <td className="py-2 text-right text-gray-400 text-xs">{t.created_at}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <p className="text-gray-400 text-sm">No transactions yet.</p>
                            )}
                        </div>

                        {/* Coupon Redemptions */}
                        <div className="bg-white shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">Coupons Used</h3>
                            {recentRedemptions.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full text-sm">
                                        <thead>
                                            <tr className="border-b text-left text-gray-500">
                                                <th className="pb-2 font-medium">Coupon</th>
                                                <th className="pb-2 font-medium text-right">Discount</th>
                                                <th className="pb-2 font-medium text-right">Bill</th>
                                                <th className="pb-2 font-medium text-right">When</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y">
                                            {recentRedemptions.map(r => (
                                                <tr key={r.id}>
                                                    <td className="py-2 text-gray-900">{r.coupon_title ?? '—'}</td>
                                                    <td className="py-2 text-right font-medium text-green-600"><CurrencySymbol />{r.discount_applied.toFixed(2)}</td>
                                                    <td className="py-2 text-right text-gray-600">{r.bill_amount ? <><CurrencySymbol />{r.bill_amount.toFixed(2)}</> : '—'}</td>
                                                    <td className="py-2 text-right text-gray-400 text-xs">{r.created_at}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <p className="text-gray-400 text-sm">No coupons redeemed yet.</p>
                            )}
                        </div>
                    </div>

                    {/* Activity Log */}
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 className="text-lg font-semibold text-gray-900 mb-4">Activity Log</h3>
                        {activityLogs.length > 0 ? (
                            <ul className="space-y-3">
                                {activityLogs.map(log => (
                                    <li key={log.id} className="flex items-start gap-3 text-sm">
                                        <span className="mt-1 flex-shrink-0 w-2 h-2 rounded-full bg-indigo-400" />
                                        <div className="flex-1">
                                            <p className="text-gray-900">
                                                <span className="font-medium capitalize">{log.event ?? 'action'}</span>
                                                {log.subject_type && <span className="text-gray-500"> on {log.subject_type}</span>}
                                                {' — '}
                                                {log.description}
                                            </p>
                                            <p className="text-xs text-gray-400">{log.created_at}</p>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p className="text-gray-400 text-sm">No activity yet.</p>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function StatCard({ label, value, color }) {
    const colorMap = {
        indigo: 'bg-indigo-50 text-indigo-600',
        green: 'bg-green-50 text-green-600',
        emerald: 'bg-emerald-50 text-emerald-600',
        amber: 'bg-amber-50 text-amber-600',
        rose: 'bg-rose-50 text-rose-600',
    };

    return (
        <div className={`rounded-lg p-4 text-center ${colorMap[color] ?? 'bg-gray-50 text-gray-600'}`}>
            <p className="text-2xl font-bold">{value}</p>
            <p className="text-xs mt-1">{label}</p>
        </div>
    );
}
