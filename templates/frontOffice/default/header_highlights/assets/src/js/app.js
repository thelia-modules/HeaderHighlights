import '@splidejs/splide/dist/css/splide.min.css';

import Splide from '@splidejs/splide';

const HomeCarousel = () => {
    if (!document.querySelector('.HomeCarousel')) {
        return;
    }

    new Splide('.HomeCarousel', {
        arrows: false,
        pagination: true,
        type: 'loop',
        speed: '1000',
        interval: window?.SLIDER_SPEED ? window.SLIDER_SPEED : '4500',
        autoplay: true,
        cover: true,
        focusableNodes: 'a',
        slideFocus: true
    })
        .on('mounted', () => document.body.classList.remove('no-js'))
        .mount();
};

export default HomeCarousel;
