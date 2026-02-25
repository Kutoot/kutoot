import { useRef } from 'react';
import html2canvas from 'html2canvas';

/**
 * StampTicket — Compact landscape ticket with side-notch circles.
 */
export default function StampTicket({ stamp }) {
    const sourceConfig = {
        'Plan Purchase':     { label: 'PLAN' },
        'Coupon Redemption': { label: 'COUPON' },
    };
    const src = sourceConfig[stamp.source] ?? { label: 'OTHER' };
    const ticketRef = useRef(null);

    const downloadAsPng = async () => {
        if (!ticketRef.current) return;
        const canvas = await html2canvas(ticketRef.current, { scale: 3, useCORS: true, backgroundColor: null });
        const link = document.createElement('a');
        link.download = `stamp-${stamp.code}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    };

    const campaignId = stamp.campaign?.id ?? stamp.campaign_id ?? null;

    return (
        <div className="w-full px-4 py-2">
            {/* ── Compact landscape ticket ── */}
            <div ref={ticketRef} className="flex w-full shadow-lg border-2 border-red-600 rounded-lg overflow-hidden" style={{ height: '80px' }}>

                {/* ── Left 20%: logo + campaign ID horizontal below ── */}
                <div className="w-1/5 bg-slate-100 flex flex-col items-center justify-center gap-1 py-2">
                    <img
                        src="/images/kutoot-initial-logo.svg"
                        alt="kutoot"
                        className="w-8 h-auto"
                    />
                    {campaignId && (
                        <p className="text-slate-400 font-mono font-bold text-[7px] tracking-widest">
                            #{campaignId}
                        </p>
                    )}
                </div>

                {/* ── Vertical perforation with notch circles ── */}
                <div className="relative w-5 bg-slate-100 flex items-center justify-center">
                    <div className="h-full border-l-2 border-dashed border-orange-300" />
                    {/* Top notch circle — on top edge */}
                    <div className="absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-white rounded-full z-10" />
                    {/* Bottom notch circle — on bottom edge */}
                    <div className="absolute -bottom-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-white rounded-full z-10" />
                </div>

                {/* ── Right 80%: stamp code ── */}
                <div className="flex-1 bg-red-600 flex flex-col justify-center px-5">
                    <p className="text-red-200 text-[7px] font-semibold uppercase tracking-widest mb-1">Stamp Code</p>
                    <p className="text-white font-mono text-xs font-bold tracking-wider leading-snug">{stamp.code}</p>
                    <p className="text-orange-300 text-[8px] font-bold uppercase tracking-widest mt-1">{src.label}</p>
                </div>
            </div>

            {/* ── Download button ── */}
            <div className="flex justify-end mt-1 px-1">
                <button
                    onClick={downloadAsPng}
                    className="text-[10px] text-red-500 hover:text-red-700 font-semibold flex items-center gap-1 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                    </svg>
                    Save as PNG
                </button>
            </div>
        </div>
    );
}
