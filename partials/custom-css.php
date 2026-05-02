<style>
  /* Page transition */
  #content {
    transition: opacity 0.18s ease;
  }
  #content.rtr-fading {
    opacity: 0;
  }

  /* Apple-style breathe loader */
  .rtr-loader {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 260px;
  }
  .rtr-loader img {
    width: 48px;
    height: 48px;
    animation: rtr-breathe 1.6s ease-in-out infinite;
    filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.12));
  }
  @keyframes rtr-breathe {
    0%, 100% { opacity: 0.4; transform: scale(0.94); }
    50%       { opacity: 1;   transform: scale(1.06); }
  }
</style>
