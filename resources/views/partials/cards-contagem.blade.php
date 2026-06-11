<div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:16px; margin-top:8px;">

    {{-- Abertas --}}
    <div style="background:white; border-radius:12px; padding:20px 24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.07); text-align:center;">
        <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:10px;">
            <span style="width:12px; height:12px; background:#f5a623; border-radius:50%; display:inline-block;"></span>
            <span style="font-size:0.82rem; font-weight:600; color:#555;">Ordens Abertas</span>
        </div>
        <div style="font-size:2rem; font-weight:700; color:#222;">
            {{ str_pad($abertas, 2, '0', STR_PAD_LEFT) }}
        </div>
    </div>

    {{-- Em Andamento --}}
    <div style="background:white; border-radius:12px; padding:20px 24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.07); text-align:center;">
        <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:10px;">
            <span style="width:12px; height:12px; background:#1a35a8; border-radius:50%; display:inline-block;"></span>
            <span style="font-size:0.82rem; font-weight:600; color:#555;">Ordens Em Andamento</span>
        </div>
        <div style="font-size:2rem; font-weight:700; color:#222;">
            {{ str_pad($em_andamento, 2, '0', STR_PAD_LEFT) }}
        </div>
    </div>

    {{-- Finalizadas --}}
    <div style="background:white; border-radius:12px; padding:20px 24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.07); text-align:center;">
        <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:10px;">
            <span style="width:12px; height:12px; background:#27ae60; border-radius:50%; display:inline-block;"></span>
            <span style="font-size:0.82rem; font-weight:600; color:#555;">Ordens Finalizadas</span>
        </div>
        <div style="font-size:2rem; font-weight:700; color:#222;">
            {{ str_pad($finalizadas, 2, '0', STR_PAD_LEFT) }}
        </div>
    </div>

</div>
