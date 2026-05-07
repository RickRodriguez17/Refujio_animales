/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{js,ts,jsx,tsx}"],
  theme: {
    extend: {
      colors: {
        refugio: {
          rojo: "#c53030",
          rojoDark: "#9b2c2c",
          amarillo: "#f5d76e",
          amarilloDark: "#e2b714",
          crema: "#fff8ef",
        },
      },
      fontFamily: {
        sans: ["Inter", "system-ui", "sans-serif"],
      },
    },
  },
  plugins: [],
};
