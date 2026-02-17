import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import CurrencySymbol from '@/Components/CurrencySymbol';


export default function Index({ auth, plans, currentSubscription, isLoggedIn }) {
    const currentPlanIndex = plans.findIndex(p => p.id === currentSubscription?.plan_id);

    const handleUpgrade = (planId) => {
        if (confirm('Are you sure you want to upgrade to this plan?')) {
            router.post(route('subscriptions.upgrade'), { plan_id: planId });
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Subscription Plans</h2>}
        >
            <Head title="Subscriptions" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {plans.map((plan) => (
                            <div key={plan.id} className={`bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 ${currentSubscription?.plan_id === plan.id ? 'border-indigo-500' : 'border-transparent'}`}>
                                <div className="p-6">
                                    <h3 className="text-2xl font-bold text-gray-900 mb-2">{plan.name}</h3>

                                    <div className="flex gap-3 mb-4">
                                        <div className="flex-1 bg-indigo-50 rounded-lg p-3 text-center">
                                            <p className="text-2xl font-bold text-indigo-600">{plan.stamps_on_purchase}</p>
                                            <p className="text-xs text-gray-500">Stamps on Purchase</p>
                                        </div>
                                        <div className="flex-1 bg-indigo-50 rounded-lg p-3 text-center">
                                            <p className="text-2xl font-bold text-indigo-600">{plan.stamps_per_100}</p>
                                            <p className="text-xs text-gray-500">Stamps per <CurrencySymbol />100 Bill</p>
                                        </div>
                                    </div>

                                    <ul className="text-sm text-gray-600 space-y-2 mb-6">
                                        <li className="flex justify-between">
                                            <span>Max Discounted Bills</span>
                                            <span className="font-medium">{plan.max_discounted_bills}</span>
                                        </li>
                                        <li className="flex justify-between">
                                            <span>Max Redeemable Amount</span>
                                            <span className="font-medium"><CurrencySymbol />{parseFloat(plan.max_redeemable_amount).toFixed(2)}</span>
                                        </li>
                                        <li className="flex justify-between">
                                            <span>Validity</span>
                                            <span className="font-medium">{plan.duration_days ? `${plan.duration_days} days` : '∞'}</span>
                                        </li>
                                    </ul>

                                    {currentSubscription?.plan_id === plan.id ? (
                                        <div>
                                            <button disabled className="w-full bg-indigo-100 text-indigo-700 font-bold py-2 px-4 rounded cursor-not-allowed">
                                                Current Plan
                                            </button>
                                            {currentSubscription.expires_at && (
                                                <p className="text-center text-xs text-gray-400 mt-1">Expires: {currentSubscription.expires_at}</p>
                                            )}
                                        </div>
                                    ) : plan.is_default ? (
                                        <p className="w-full text-center text-xs text-gray-400 py-2">Auto-assigned when plan expires</p>
                                    ) : !isLoggedIn ? (
                                        <Link
                                            href={route('login')}
                                            className="w-full block text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-200"
                                        >
                                            Login to Upgrade
                                        </Link>
                                    ) : plans.indexOf(plan) > currentPlanIndex ? (
                                        <button
                                            onClick={() => handleUpgrade(plan.id)}
                                            className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-200"
                                        >
                                            Upgrade
                                        </button>
                                    ) : (
                                        <p className="w-full text-center text-xs text-gray-400 py-2">Lower tier</p>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
