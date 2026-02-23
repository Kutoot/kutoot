import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import StampTicket from '@/Components/StampTicket';
import EmptyState from '@/Components/EmptyState';
import { useState, useEffect, useCallback } from 'react';
import axios from 'axios';

export default function StampsIndex({ auth, stamps: initialStamps, stampGroups, primaryCampaign, totalStamps }) {
    const [stamps, setStamps] = useState(initialStamps);
    const [editingStamp, setEditingStamp] = useState(null);
    const [slotValues, setSlotValues] = useState([]);
    const [editError, setEditError] = useState('');
    const [editSubmitting, setEditSubmitting] = useState(false);
    const [activeCampaign, setActiveCampaign] = useState(null);

    // Recompute groups from current stamps state so edits reflect immediately
    const computedGroups = (() => {
        const groups = {};
        stamps.forEach(s => {
            const key = s.campaign_name ?? 'No Campaign';
            if (!groups[key]) {
                groups[key] = [];
            }
            groups[key].push(s);
        });
        return groups;
    })();

    const campaignNames = Object.keys(stampGroups ?? {});

    const openEditModal = useCallback((stamp) => {
        const config = stamp.stamp_config;
        if (!config) return;
        setEditingStamp(stamp);
        setSlotValues(Array(config.slots).fill(config.min));
        setEditError('');
    }, []);

    const closeEditModal = useCallback(() => {
        setEditingStamp(null);
        setSlotValues([]);
        setEditError('');
    }, []);

    const handleSlotChange = useCallback((index, value) => {
        setSlotValues(prev => {
            const next = [...prev];
            next[index] = parseInt(value) || 0;
            return next;
        });
    }, []);

    const submitStampEdit = useCallback(async () => {
        if (!editingStamp) return;
        setEditSubmitting(true);
        setEditError('');
        try {
            const response = await axios.patch(`/api/stamps/${editingStamp.id}/code`, {
                slot_values: slotValues,
            });
            setStamps(prev => prev.map(s =>
                s.id === editingStamp.id ? { ...s, code: response.data.stamp.code, is_editable: true } : s
            ));
            closeEditModal();
        } catch (err) {
            setEditError(err.response?.data?.message || 'Failed to update stamp code.');
        } finally {
            setEditSubmitting(false);
        }
    }, [editingStamp, slotValues, closeEditModal]);

    const previewCode = editingStamp?.stamp_config ? (() => {
        const config = editingStamp.stamp_config;
        const digits = String(config.max).length;
        const code = editingStamp.code.split('-')[0] || 'CODE';
        const paddedSlots = slotValues.map(v => String(v).padStart(digits, '0'));
        return code + '-' + paddedSlots.join('-');
    })() : '';

    // Which stamps to display: filtered by campaign or all
    const displayedStamps = activeCampaign
        ? (computedGroups[activeCampaign] ?? [])
        : stamps;

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-bold leading-tight text-white flex items-center gap-2">
                    🎫 My Stamps
                    <span className="text-sm font-normal opacity-80">({totalStamps})</span>
                </h2>
            }
        >
            <Head title="My Stamps" />

            <div className="py-6 sm:py-8">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                    {/* Campaign filter tabs */}
                    {campaignNames.length > 0 && (
                        <div className="flex gap-2 flex-wrap">
                            <button
                                onClick={() => setActiveCampaign(null)}
                                className={`px-4 py-2 rounded-full text-sm font-bold transition-all ${
                                    activeCampaign === null
                                        ? 'lucky-gradient text-white shadow-md'
                                        : 'bg-white border-2 border-dashed border-lucky-200 text-gray-600 hover:bg-lucky-50'
                                }`}
                            >
                                All ({totalStamps})
                            </button>
                            {campaignNames.map(name => (
                                <button
                                    key={name}
                                    onClick={() => setActiveCampaign(name)}
                                    className={`px-4 py-2 rounded-full text-sm font-bold transition-all flex items-center gap-1.5 ${
                                        activeCampaign === name
                                            ? 'lucky-gradient text-white shadow-md'
                                            : name === primaryCampaign
                                                ? 'bg-white border-2 border-lucky-400 text-lucky-700 hover:bg-lucky-50'
                                                : 'bg-white border-2 border-dashed border-lucky-200 text-gray-600 hover:bg-lucky-50'
                                    }`}
                                >
                                    {name === primaryCampaign && <span>⭐</span>}
                                    {name} ({(stampGroups[name] ?? []).length})
                                </button>
                            ))}
                        </div>
                    )}

                    {/* Grouped stamp display */}
                    {stamps.length > 0 ? (
                        activeCampaign ? (
                            /* Single campaign view */
                            <CampaignStampGroup
                                campaignName={activeCampaign}
                                stamps={displayedStamps}
                                isPrimary={activeCampaign === primaryCampaign}
                                onEdit={openEditModal}
                            />
                        ) : (
                            /* All campaigns grouped */
                            Object.entries(computedGroups).map(([campaignName, groupStamps]) => (
                                <CampaignStampGroup
                                    key={campaignName}
                                    campaignName={campaignName}
                                    stamps={groupStamps}
                                    isPrimary={campaignName === primaryCampaign}
                                    onEdit={openEditModal}
                                />
                            ))
                        )
                    ) : (
                        <div className="coupon-card p-6 border-lucky-300 bg-gradient-to-br from-lucky-50/30 via-white to-lucky-50/30">
                            <EmptyState
                                icon="🎟️"
                                title="No lottery tickets yet"
                                description="Earn tickets by purchasing a plan or redeeming coupons at partner stores."
                                actionLabel="Browse Coupons"
                                actionHref={route('coupons.index')}
                            />
                        </div>
                    )}
                </div>
            </div>

            {/* Stamp Edit Modal */}
            {editingStamp && editingStamp.stamp_config && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" onClick={closeEditModal}>
                    <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative" onClick={e => e.stopPropagation()}>
                        <button onClick={closeEditModal} className="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                        <h3 className="font-display text-lg text-gray-900 mb-1 flex items-center gap-2">
                            🎯 Pick Your Numbers
                        </h3>
                        <p className="text-xs text-gray-500 mb-4">
                            Choose {editingStamp.stamp_config.slots} numbers between {editingStamp.stamp_config.min} and {editingStamp.stamp_config.max} in ascending order.
                        </p>

                        {/* Live Preview */}
                        <div className="bg-lucky-50 rounded-xl p-3 mb-4 text-center border border-lucky-200">
                            <p className="text-xs text-lucky-600 font-medium mb-1">Preview</p>
                            <p className="font-mono text-lg font-bold text-lucky-700">{previewCode}</p>
                        </div>

                        {/* Slot Inputs */}
                        <div className="grid grid-cols-3 gap-2 mb-4">
                            {slotValues.map((val, idx) => (
                                <div key={idx}>
                                    <label className="text-xs text-gray-500 font-medium">Slot {idx + 1}</label>
                                    <input
                                        type="number"
                                        min={editingStamp.stamp_config.min}
                                        max={editingStamp.stamp_config.max}
                                        value={val}
                                        onChange={(e) => handleSlotChange(idx, e.target.value)}
                                        className="w-full rounded-lg border-gray-300 text-center font-mono text-sm focus:border-lucky-500 focus:ring-lucky-500"
                                    />
                                </div>
                            ))}
                        </div>

                        {editError && (
                            <div className="bg-red-50 text-red-700 text-xs rounded-lg p-2.5 mb-3 border border-red-200">
                                {editError}
                            </div>
                        )}

                        {editingStamp.editable_until && (
                            <div className="text-center mb-3">
                                <StampCountdown editableUntil={editingStamp.editable_until} showLabel />
                            </div>
                        )}

                        <div className="flex gap-2">
                            <button
                                onClick={closeEditModal}
                                className="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                onClick={submitStampEdit}
                                disabled={editSubmitting}
                                className="flex-1 px-4 py-2.5 rounded-xl lucky-gradient text-white text-sm font-bold shadow-md hover:shadow-lg transition-all disabled:opacity-50"
                            >
                                {editSubmitting ? 'Saving...' : 'Confirm Numbers'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}

function CampaignStampGroup({ campaignName, stamps, isPrimary, onEdit }) {
    if (!stamps || stamps.length === 0) return null;

    return (
        <div className={`coupon-card overflow-hidden ${isPrimary ? 'ring-2 ring-lucky-400' : ''}`}>
            <div className="p-5 sm:p-6 pb-0 sm:pb-0">
                <h3 className="font-display text-lg text-gray-900 mb-4 flex items-center gap-2">
                    {isPrimary && <span className="text-lg">⭐</span>}
                    <span>{campaignName}</span>
                    <span className="text-sm font-normal text-gray-400">({stamps.length} stamps)</span>
                    {isPrimary && (
                        <span className="ml-auto text-xs font-bold bg-lucky-100 text-lucky-700 px-2.5 py-0.5 rounded-full">Primary</span>
                    )}
                </h3>
            </div>

            {/* Ticket grid — responsive from 1 col to 4 */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 px-5 sm:px-6 pb-5 sm:pb-6">
                {stamps.map(s => (
                    <StampTicket key={s.id} stamp={s} onEdit={onEdit} />
                ))}
            </div>
        </div>
    );
}

function StampCountdown({ editableUntil, showLabel = false }) {
    const [remaining, setRemaining] = useState('');

    useEffect(() => {
        const update = () => {
            const diff = new Date(editableUntil) - new Date();
            if (diff <= 0) {
                setRemaining('Expired');
                return;
            }
            const mins = Math.floor(diff / 60000);
            const secs = Math.floor((diff % 60000) / 1000);
            setRemaining(`${mins}:${String(secs).padStart(2, '0')}`);
        };
        update();
        const timer = setInterval(update, 1000);
        return () => clearInterval(timer);
    }, [editableUntil]);

    const isExpired = remaining === 'Expired';

    return (
        <span className={`inline-flex items-center gap-1 text-xs font-mono ${isExpired ? 'text-red-500' : 'text-amber-600'}`}>
            {showLabel && <span className="font-sans font-medium">Time left:</span>}
            <span>⏱️ {remaining}</span>
        </span>
    );
}
