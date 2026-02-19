import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function Register({ status, debugOtp }) {
    const [otpSent, setOtpSent] = useState(false);

    const registerForm = useForm({
        name: '',
        email: '',
        mobile: '',
    });

    const verifyForm = useForm({
        otp: '',
    });

    useEffect(() => {
        if (debugOtp) {
            verifyForm.setData('otp', debugOtp);
            setOtpSent(true);
        }
    }, [debugOtp]);

    const handleSendOtp = (e) => {
        e.preventDefault();

        registerForm.post(route('register.send-otp'), {
            preserveScroll: true,
            onSuccess: (page) => {
                setOtpSent(true);

                if (page.props.debugOtp) {
                    verifyForm.setData('otp', page.props.debugOtp);
                }
            },
        });
    };

    const handleVerify = (e) => {
        e.preventDefault();

        verifyForm.post(route('register.verify'), {
            onFinish: () => verifyForm.reset('otp'),
        });
    };

    const handleResendOtp = () => {
        registerForm.post(route('register.send-otp'), {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.debugOtp) {
                    verifyForm.setData('otp', page.props.debugOtp);
                }
            },
        });
    };

    const handleBack = () => {
        setOtpSent(false);
        verifyForm.reset();
    };

    return (
        <GuestLayout>
            <Head title="Register" />

            {status && (
                <div className="mb-4 text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            {!otpSent ? (
                <form onSubmit={handleSendOtp}>
                    <div className="mb-4 text-center">
                        <h2 className="text-lg font-semibold text-lucky-700">
                            Create Account
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            We'll verify your mobile number with OTP.
                        </p>
                    </div>

                    <div>
                        <InputLabel htmlFor="name" value="Name" />
                        <TextInput
                            id="name"
                            name="name"
                            value={registerForm.data.name}
                            className="mt-1 block w-full"
                            autoComplete="name"
                            isFocused={true}
                            onChange={(e) =>
                                registerForm.setData('name', e.target.value)
                            }
                        />
                        <InputError
                            message={registerForm.errors.name}
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-4">
                        <InputLabel htmlFor="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            name="email"
                            value={registerForm.data.email}
                            className="mt-1 block w-full"
                            autoComplete="username"
                            onChange={(e) =>
                                registerForm.setData('email', e.target.value)
                            }
                        />
                        <InputError
                            message={registerForm.errors.email}
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-4">
                        <InputLabel htmlFor="mobile" value="Mobile Number" />
                        <TextInput
                            id="mobile"
                            type="tel"
                            name="mobile"
                            value={registerForm.data.mobile}
                            className="mt-1 block w-full"
                            autoComplete="tel"
                            placeholder="9876543210"
                            onChange={(e) =>
                                registerForm.setData('mobile', e.target.value)
                            }
                        />
                        <InputError
                            message={registerForm.errors.mobile}
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-4 flex items-center justify-between">
                        <Link
                            href={route('login')}
                            className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-lucky-500 focus:ring-offset-2"
                        >
                            Already registered?
                        </Link>

                        <PrimaryButton disabled={registerForm.processing}>
                            Send OTP
                        </PrimaryButton>
                    </div>
                </form>
            ) : (
                <form onSubmit={handleVerify}>
                    <div className="mb-4 text-center">
                        <h2 className="text-lg font-semibold text-lucky-700">
                            Verify Mobile
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            Enter the 6-digit code sent to{' '}
                            <span className="font-medium text-lucky-600">
                                {registerForm.data.mobile}
                            </span>
                        </p>
                    </div>

                    <div>
                        <InputLabel htmlFor="otp" value="One-Time Password" />
                        <TextInput
                            id="otp"
                            type="text"
                            name="otp"
                            value={verifyForm.data.otp}
                            className="mt-1 block w-full text-center text-2xl tracking-[0.5em]"
                            isFocused={true}
                            maxLength={6}
                            placeholder="000000"
                            autoComplete="one-time-code"
                            onChange={(e) =>
                                verifyForm.setData(
                                    'otp',
                                    e.target.value.replace(/\D/g, ''),
                                )
                            }
                        />
                        <InputError
                            message={verifyForm.errors.otp}
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-4 flex items-center justify-between">
                        <div className="flex flex-col gap-1">
                            <button
                                type="button"
                                onClick={handleResendOtp}
                                disabled={registerForm.processing}
                                className="text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none"
                            >
                                Resend OTP
                            </button>
                            <button
                                type="button"
                                onClick={handleBack}
                                className="text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none"
                            >
                                Edit details
                            </button>
                        </div>

                        <PrimaryButton disabled={verifyForm.processing}>
                            Verify & Register
                        </PrimaryButton>
                    </div>
                </form>
            )}
        </GuestLayout>
    );
}
