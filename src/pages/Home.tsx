import React from 'react';
import { Link } from 'react-router-dom';

const Home: React.FC = () => {
  return (
    <div className="public-route">
      {/* Hero Section */}
      <div className="hero-section">
        <div className="container">
          <div className="row justify-content-center">
            <div className="col-md-8 text-center">
              <h1 className="display-4 mb-4">CleanAid</h1>
              <p className="lead mb-4">
                Streamline your data cleansing process with AI-powered duplicate detection
                and automated review workflows.
              </p>
              <div className="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <Link to="/login" className="btn btn-danger btn-lg px-4 gap-3">
                  Get Started
                </Link>
                <Link to="/signup" className="btn btn-outline-light btn-lg px-4">
                  Learn More
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Features Section */}
      <div className="features-section">
        <div className="container">
          <div className="row g-4">
            <div className="col-md-4">
              <div className="card h-100">
                <div className="card-body text-center">
                  <i className="bi bi-search display-4 text-primary mb-3"></i>
                  <h3 className="card-title h5">Duplicate Detection</h3>
                  <p className="card-text">
                    Advanced algorithms to identify and flag potential duplicate records
                    in your datasets.
                  </p>
                </div>
              </div>
            </div>
            <div className="col-md-4">
              <div className="card h-100">
                <div className="card-body text-center">
                  <i className="bi bi-robot display-4 text-primary mb-3"></i>
                  <h3 className="card-title h5">AI-Powered Review</h3>
                  <p className="card-text">
                    Smart suggestions and automated workflows to streamline the review
                    process.
                  </p>
                </div>
              </div>
            </div>
            <div className="col-md-4">
              <div className="card h-100">
                <div className="card-body text-center">
                  <i className="bi bi-cloud-arrow-down display-4 text-primary mb-3"></i>
                  <h3 className="card-title h5">Export & Integration</h3>
                  <p className="card-text">
                    Seamlessly export cleansed data and integrate with your existing
                    systems.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Footer */}
      <footer className="footer mt-auto py-3">
        <div className="container text-center">
          <p className="mb-0">
            © {new Date().getFullYear()} Department of Social Welfare and Development (DSWD)
          </p>
        </div>
      </footer>
    </div>
  );
};

export default Home; 