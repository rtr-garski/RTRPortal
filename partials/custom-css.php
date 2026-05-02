<style>
  /* Page transition */
  #content {
    transition: opacity 0.18s ease;
  }
  #content.rtr-fading {
    opacity: 0;
  }

  /* Flip loader */
  .rtr-loader {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 260px;
    perspective: 400px;
  }
  .rtr-loader img {
    width: 46px;
    height: 50px;
    animation: rtr-flip 1.4s ease-in-out infinite;
  }
  @keyframes rtr-flip {
    0%   { transform: rotateY(0deg);   opacity: 1; }
    45%  { transform: rotateY(90deg);  opacity: 0; }
    55%  { transform: rotateY(90deg);  opacity: 0; }
    100% { transform: rotateY(360deg); opacity: 1; }
  }
</style>
