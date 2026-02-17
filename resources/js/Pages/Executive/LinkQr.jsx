import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Transition } from '@headlessui/react';

export default function LinkQr({ auth, locations }) {
    const { data, setData, post, processing, errors, recentlySuccessful, reset } = useForm({
        unique_code: '',
        merchant_location_id: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('executive.qr.link'), {
            onSuccess: () => reset('unique_code'),
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Link QR Sticker</h2>}
        >
            <Head title="Link QR Sticker" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <header>
                                <h2 className="text-lg font-medium text-gray-900">Link a Physical QR Code</h2>
                                <p className="mt-1 text-sm text-gray-600">
                                    Enter the code printed on the Kutoot sticker and select the merchant location to link it.
                                </p>
                            </header>

                            <form onSubmit={submit} className="mt-6 space-y-6 max-w-xl">
                                <div>
                                    <InputLabel htmlFor="unique_code" value="Sticker Code (e.g. KUT-0001)" />
                                    <TextInput
                                        id="unique_code"
                                        className="mt-1 block w-full uppercase"
                                        value={data.unique_code}
                                        onChange={(e) => setData('unique_code', e.target.value)}
                                        required
                                        autoFocus
                                        placeholder="KUT-XXXX"
                                    />
                                    <InputError className="mt-2" message={errors.unique_code} />
                                </div>

                                <div>
                                    <InputLabel htmlFor="merchant_location_id" value="Merchant Location" />
                                    <select
                                        id="merchant_location_id"
                                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        value={data.merchant_location_id}
                                        onChange={(e) => setData('merchant_location_id', e.target.value)}
                                        required
                                    >
                                        <option value="">Select a location</option>
                                        {locations.map((loc) => (
                                            <option key={loc.id} value={loc.id}>
                                                {loc.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError className="mt-2" message={errors.merchant_location_id} />
                                </div>

                                <div className="flex items-center gap-4">
                                    <PrimaryButton disabled={processing}>Link QR Code</PrimaryButton>

                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-gray-600">Linked successfully.</p>
                                    </Transition>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
