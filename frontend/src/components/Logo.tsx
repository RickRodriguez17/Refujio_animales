export function Logo({ size = 40 }: { size?: number }) {
  return (
    <div className="flex items-center gap-3">
      <div
        className="flex items-center justify-center rounded-full bg-refugio-rojo text-white shadow"
        style={{ width: size, height: size }}
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 64 64"
          width={size * 0.65}
          height={size * 0.65}
          fill="currentColor"
          aria-hidden
        >
          <path d="M20 26a6 6 0 1 1 0-12 6 6 0 0 1 0 12zm24 0a6 6 0 1 1 0-12 6 6 0 0 1 0 12zm-32 12a6 6 0 1 1 0-12 6 6 0 0 1 0 12zm40 0a6 6 0 1 1 0-12 6 6 0 0 1 0 12zM32 56c-9 0-16-7-16-15 0-7 7-13 16-13s16 6 16 13c0 8-7 15-16 15z" />
        </svg>
      </div>
      <div className="leading-tight">
        <div className="font-extrabold text-refugio-rojo text-lg">
          Refugio Amor de 4 Patas
        </div>
        <div className="text-xs text-slate-500">Un solo corazón</div>
      </div>
    </div>
  );
}
