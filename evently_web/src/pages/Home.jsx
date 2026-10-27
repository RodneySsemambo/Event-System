import Navbar from '../components/Navbar';
import Hero from '../components/Hero';
import ImageTextSection from '../components/ImageTextSection';
import HowItWorks from '../components/HowItWorks';
import './Home.css';
import Testimonials from '../components/Testimonials';
import Footer from '../components/Footer';
import Showcase from '../components/Showcase';

export default function Home() {
  return (
    <div className="home">
      <Navbar />
      <Hero />
      <ImageTextSection />
      <Showcase />
            <HowItWorks />

      <Testimonials/>
     
<Footer/>
      
    </div>
  );
}
