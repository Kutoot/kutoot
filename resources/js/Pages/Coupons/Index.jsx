import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import CurrencySymbol from '@/Components/CurrencySymbol';
import { usePage } from '@inertiajs/react';


export default function Index({ auth, coupons, locations, planName }) {
    const { platform_fee, gst_rate, platform_fee_type } = usePage().props;

    const [confirmingRedemption, setConfirmingRedemption] = useState(false);
    const [selectedCoupon, setSelectedCoupon] = useState(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        merchant_location_id: '',
        amount: '',
    });

    useEffect(() => {
        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        document.body.appendChild(script);

        return () => {
            document.body.removeChild(script);
        }
    }, []);


    const confirmRedemption = (coupon) => {
        setSelectedCoupon(coupon);
        setConfirmingRedemption(true);
        if (coupon.merchant_location_id) {
            setData('merchant_location_id', coupon.merchant_location_id);
        } else {
            setData('merchant_location_id', '');
        }
    };

    const closeModal = () => {
        setConfirmingRedemption(false);
        reset();
    };

    const redeemCoupon = (e) => {
        e.preventDefault();

        post(route('coupons.redeem', selectedCoupon.id), {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.flash?.order || page.props.order) {
                    // Logic for JSON response if handled by Inertia as visit
                    // But we used response()->json() in controller which isn't ideal for Inertia partials
                    // Let's adjust controller to use Inertia::render or handle it here
                }
            },
            onError: (err) => console.error(err),
            // Custom handler because we are returning JSON from a POST
            onFinish: () => { },
        });
    };

    // Re-implementing redeemCoupon to handle the JSON response manually since we're using a payment gateway
    const initiatePayment = async (e) => {
        e.preventDefault();

        try {
            const response = await fetch(route('coupons.redeem', selectedCoupon.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (response.ok) {
                handleRazorpayPayment(result.order, result.transaction_id);
            } else {
                alert(result.error || 'Something went wrong');
            }
        } catch (error) {
            console.error('Payment initiation failed', error);
        }
    };

    const handleRazorpayPayment = (order, transactionId) => {
        const options = {
            key: order.key,
            amount: order.amount,
            currency: order.currency,
            name: "Kutoot",
            description: `Payment for ${selectedCoupon.title}`,
            order_id: order.id,
            handler: function (response) {
                router.post(route('coupons.verify-payment', transactionId), {
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                });
            },
            prefill: {
                name: auth.user.name,
                email: auth.user.email,
            },
            theme: {
                color: "#4f46e5",
            },
        };

        const rzp = new window.Razorpay(options);
        rzp.open();
    };

    const calculateBreakdown = () => {
        const billAmount = parseFloat(data.amount) || 0;
        let discount = 0;
        if (selectedCoupon) {
            if (selectedCoupon.discount_type === 'percentage') {
                discount = (billAmount * selectedCoupon.discount_value) / 100;
            } else {
                discount = selectedCoupon.discount_value;
            }
        }
        const finalBill = Math.max(0, billAmount - discount);
        const fee = parseFloat(platform_fee);
        // handle percentage fee if needed (frontend sync)
        const feeAmount = platform_fee_type === 'percentage' ? (billAmount * fee / 100) : fee;
        const gst = (feeAmount * gst_rate) / 100;
        const total = finalBill + feeAmount + gst;

        return { billAmount, discount, finalBill, feeAmount, gst, total };
    };

    const breakdown = calculateBreakdown();


    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">My Coupons ({planName})</h2>}
        >
            <Head title="Coupons" />

            <div className="py-12 bg-gray-50 min-h-screen">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {coupons.data.length > 0 ? (
                            coupons.data.map((coupon) => (
                                <div key={coupon.id} className="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 flex flex-col">
                                    <div className="p-6 flex-grow">
                                        <span className="inline-block px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full mb-2">
                                            {coupon.merchant_location ? coupon.merchant_location.branch_name : 'Global Coupon'}
                                        </span>
                                        <h3 className="text-lg font-bold text-gray-900 mb-1">{coupon.title}</h3>
                                        <p className="text-gray-600 text-sm mb-4">{coupon.description}</p>

                                        <div className="bg-gray-50 p-3 rounded text-sm text-gray-700">
                                            <div className="flex justify-between mb-1">
                                                <span>Code:</span>
                                                <span className="font-mono font-bold">{coupon.code}</span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span>Value:</span>
                                                <span className="font-bold text-indigo-600">
                                                    {coupon.discount_type === 'percentage' ? `${coupon.discount_value}% Off` : <><CurrencySymbol />{coupon.discount_value} Off</>}
                                                </span>

                                            </div>
                                        </div>
                                    </div>
                                    <div className="bg-gray-50 px-6 py-4 border-t border-gray-100">
                                        {auth.user ? (
                                            <PrimaryButton
                                                className="w-full justify-center"
                                                onClick={() => confirmRedemption(coupon)}
                                            >
                                                Redeem Now
                                            </PrimaryButton>
                                        ) : (
                                            <Link
                                                href={route('login')}
                                                className="inline-flex items-center w-full justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                                            >
                                                Login to Redeem
                                            </Link>
                                        )}
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="col-span-full text-center py-10">
                                <h3 className="text-lg font-medium text-gray-900">No coupons available</h3>
                                <p className="mt-1 text-sm text-gray-500">Upgrade your plan to unlock more rewards.</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <Modal show={confirmingRedemption} onClose={closeModal}>
                <form onSubmit={initiatePayment} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Redeem Coupon: {selectedCoupon?.title}
                    </h2>

                    <p className="mt-1 text-sm text-gray-600">
                        To redeem this coupon, please select the store location and enter the bill amount.
                    </p>

                    <div className="mt-6">
                        <InputLabel htmlFor="merchant_location_id" value="Store Location" />

                        <select
                            id="merchant_location_id"
                            name="merchant_location_id"
                            value={data.merchant_location_id}
                            onChange={(e) => setData('merchant_location_id', e.target.value)}
                            className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="">Select a location</option>
                            {locations.map((loc) => (
                                <option key={loc.id} value={loc.id}>
                                    {loc.name}
                                </option>
                            ))}
                        </select>

                        <InputError message={errors.merchant_location_id} className="mt-2" />
                    </div>

                    <div className="mt-6">
                        <InputLabel htmlFor="amount" value={<span>Bill Amount (<CurrencySymbol />)</span>} />


                        <TextInput
                            id="amount"
                            type="number"
                            step="0.01"
                            name="amount"
                            value={data.amount}
                            onChange={(e) => setData('amount', e.target.value)}
                            className="mt-1 block w-full"
                            placeholder="0.00"
                            required
                        />
                        <InputError message={errors.amount} className="mt-2" />
                    </div>

                    <div className="mt-6 bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                        <div className="flex justify-between text-sm text-indigo-900 mb-1">
                            <span>Total Bill:</span>
                            <span><CurrencySymbol />{breakdown.billAmount.toFixed(2)}</span>
                        </div>
                        <div className="flex justify-between text-sm text-indigo-900 mb-1">
                            <span>Discount Applied:</span>
                            <span className="text-green-600">- <CurrencySymbol />{breakdown.discount.toFixed(2)}</span>
                        </div>
                        <div className="flex justify-between text-sm font-semibold text-indigo-900 mb-1 pt-1 border-t border-indigo-100">
                            <span>Bill after Discount:</span>
                            <span><CurrencySymbol />{breakdown.finalBill.toFixed(2)}</span>
                        </div>
                        <div className="flex justify-between text-sm text-indigo-900 mb-1">
                            <span>Platform Fee:</span>
                            <span><CurrencySymbol />{breakdown.feeAmount.toFixed(2)}</span>
                        </div>
                        <div className="flex justify-between text-sm text-indigo-900 mb-2">
                            <span>GST ({gst_rate}%):</span>
                            <span><CurrencySymbol />{breakdown.gst.toFixed(2)}</span>
                        </div>
                        <div className="flex justify-between font-bold text-indigo-900 pt-2 border-t border-indigo-200">
                            <span>Total to Pay:</span>
                            <span><CurrencySymbol />{breakdown.total.toFixed(2)}</span>
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end">

                        <SecondaryButton onClick={closeModal}>Cancel</SecondaryButton>
                        <PrimaryButton className="ms-3" disabled={processing}>
                            Pay <CurrencySymbol />{breakdown.total.toFixed(2)} & Redeem
                        </PrimaryButton>
                    </div>


                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
