import { useState, useEffect, useCallback } from 'react';
import { router } from '@inertiajs/react';

/**
 * Reusable hook for Razorpay payment integration.
 *
 * @param {Object} options
 * @param {Object} options.user - Current authenticated user { name, email }
 * @param {string} options.appName - Application name shown in Razorpay popup
 * @param {string} options.themeColor - Brand color for Razorpay popup
 * @param {Function} options.onSuccess - Callback after successful verification
 * @param {Function} options.onError - Callback on error
 * @param {Function} options.onClose - Callback when user closes popup without paying
 */
export default function useRazorpay({
    user = {},
    appName = 'Kutoot',
    themeColor = '#f08c10',
    onSuccess = null,
    onError = null,
    onClose = null,
} = {}) {
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState(null);
    // idle | initiating | checkout_open | verifying | success | failed
    const [paymentStatus, setPaymentStatus] = useState('idle');

    // Load Razorpay script in production mode
    useEffect(() => {
        const existingScript = document.querySelector('script[src="https://checkout.razorpay.com/v1/checkout.js"]');
        if (existingScript) return;

        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        document.body.appendChild(script);

        return () => {
            if (document.body.contains(script)) {
                document.body.removeChild(script);
            }
        };
    }, []);

    const resetState = useCallback(() => {
        setIsLoading(false);
        setError(null);
        setPaymentStatus('idle');
    }, []);

    /**
     * Open the Razorpay checkout popup with the given order details.
     */
    const openCheckout = useCallback((order, verifyRoute, extraData = {}, description = 'Payment') => {
        if (!window.Razorpay) {
            const err = 'Razorpay SDK not loaded. Please refresh the page.';
            setError(err);
            setPaymentStatus('failed');
            onError?.(err);
            return;
        }

        setPaymentStatus('checkout_open');

        const options = {
            key: order.key,
            amount: order.amount,
            currency: order.currency,
            name: appName,
            description,
            order_id: order.id,
            handler: function (response) {
                setIsLoading(true);
                setPaymentStatus('verifying');
                router.post(verifyRoute, {
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                    ...extraData,
                }, {
                    onSuccess: () => {
                        setIsLoading(false);
                        setPaymentStatus('success');
                        onSuccess?.();
                    },
                    onError: (errs) => {
                        setIsLoading(false);
                        setPaymentStatus('failed');
                        setError('Payment verification failed. Please contact support.');
                        onError?.(errs);
                    },
                });
            },
            prefill: {
                name: user?.name || '',
                email: user?.email || '',
            },
            theme: {
                color: themeColor,
            },
            modal: {
                ondismiss: () => {
                    setIsLoading(false);
                    setPaymentStatus('idle');
                    onClose?.();
                },
            },
        };

        const rzp = new window.Razorpay(options);
        rzp.on('payment.failed', function (response) {
            setPaymentStatus('failed');
            setError(response.error?.description || 'Payment failed. Please try again.');
            onError?.(response.error);
        });
        rzp.open();
    }, [appName, themeColor, user, onSuccess, onError, onClose]);

    /**
     * Initiate a payment flow.
     * Fetches the order via JSON and opens Razorpay popup.
     */
    const initiatePayment = useCallback(async ({
        orderRoute,
        orderData,
        verifyRoute,
        extraVerifyData = {},
        description = 'Payment',
    }) => {
        setIsLoading(true);
        setError(null);
        setPaymentStatus('initiating');

        // Fetch order via JSON, then open Razorpay popup
        try {
            const response = await fetch(orderRoute, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(orderData),
            });

            const result = await response.json();

            if (!response.ok) {
                const errMsg = result.error || 'Something went wrong';
                setError(errMsg);
                setIsLoading(false);
                setPaymentStatus('failed');
                onError?.(errMsg);
                return;
            }

            setIsLoading(false);
            openCheckout(result.order, verifyRoute, extraVerifyData, description);
        } catch (err) {
            setIsLoading(false);
            const errMsg = 'Payment initiation failed. Please try again.';
            setError(errMsg);
            setPaymentStatus('failed');
            onError?.(errMsg);
        }
    }, [openCheckout, onError]);

    return {
        initiatePayment,
        openCheckout,
        isLoading,
        error,
        paymentStatus,
        resetState,
        clearError: () => setError(null),
    };
}
