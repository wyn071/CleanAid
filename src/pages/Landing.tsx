import React from 'react';
import { Link } from 'react-router-dom';

const Landing: React.FC = () => {
  return (
    <div>

      {/* Hero Section */}
      <header className="bg-primary text-white text-center py-5 shadow">
        <div className="container">
          <h1 className="display-3 fw-bold">CleanAid</h1>
          <p className="lead">DSWD Region X Data Cleansing Tool for the Ayuda Program</p>
          <p className="mb-4">Ensure data accuracy and streamline beneficiary verification with ease.</p>
          <div className="d-flex justify-content-center gap-3 flex-wrap">
            <Link to="/signup" className="btn btn-light btn-lg px-4">Get Started</Link>
            <Link to="/documentation" className="btn btn-outline-light btn-lg px-4">Learn More</Link>
          </div>
        </div>
      </header>

      {/* Features Section */}
      <section className="py-5 bg-light">
        <div className="container">
          <h2 className="text-center mb-5">Why Choose CleanAid?</h2>
          <div className="row text-center g-4">
            <div className="col-md-4">
              <div className="card border-0 shadow-sm h-100">
                <div className="card-body">
                  <i className="bi bi-upload display-4 text-primary mb-3"></i>
                  <h5 className="fw-bold">Easy Data Upload</h5>
                  <p className="text-muted">Import Excel or CSV files effortlessly via drag-and-drop or file picker.</p>
                </div>
              </div>
            </div>
            <div className="col-md-4">
              <div className="card border-0 shadow-sm h-100">
                <div className="card-body">
                  <i className="bi bi-search display-4 text-success mb-3"></i>
                  <h5 className="fw-bold">Automated Cleansing</h5>
                  <p className="text-muted">Automatically detect duplicates, missing fields, and inconsistencies.</p>
                </div>
              </div>
            </div>
            <div className="col-md-4">
              <div className="card border-0 shadow-sm h-100">
                <div className="card-body">
                  <i className="bi bi-bar-chart-fill display-4 text-info mb-3"></i>
                  <h5 className="fw-bold">Insightful Reports</h5>
                  <p className="text-muted">Download audit reports for transparency and efficient data management.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* FAQ Placeholder */}
      <section className="bg-light py-5 border-top">
        <div className="container text-center">
          <h2 className="mb-3">Frequently Asked Questions</h2>
          <p className="text-muted">Coming soon: a complete list of tips and guidance to get you started.</p>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-dark text-white py-4 mt-auto">
        <div className="container text-center">
          <small>&copy; {new Date().getFullYear()} CleanAid. Developed for DSWD Region X</small>
        </div>
      </footer>

    </div>
  );
};

export default Landing;
