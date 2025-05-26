import React from 'react';
import { Button } from 'react-bootstrap';
import { Link } from 'react-router-dom';

const NotFound: React.FC = () => {
  return (
    <div className="container">
      <div className="row justify-content-center align-items-center min-vh-100">
        <div className="col-md-6 text-center">
          <h1 className="display-1 text-danger mb-4">404</h1>
          <h2 className="mb-4">Page Not Found</h2>
          <p className="text-muted mb-4">
            The page you are looking for might have been removed, had its name
            changed, or is temporarily unavailable.
          </p>
          <Link to="/dashboard">
            <Button variant="danger" size="lg">
              <i className="bi bi-house-door me-2"></i>
              Back to Dashboard
            </Button>
          </Link>
        </div>
      </div>
    </div>
  );
};

export default NotFound; 