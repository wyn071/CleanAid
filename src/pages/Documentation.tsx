import React from 'react';

const Documentation: React.FC = () => {
  return (
    <div className="container-fluid py-4">
      <div className="row">
        {/* Sidebar Navigation */}
        <div className="col-md-3">
          <div className="card shadow-sm">
            <div className="card-header bg-primary text-white">
              <h5 className="mb-0">Documentation</h5>
            </div>
            <div className="list-group list-group-flush">
              <a href="#overview" className="list-group-item list-group-item-action active">
                Overview
              </a>
              <a href="#getting-started" className="list-group-item list-group-item-action">
                Getting Started
              </a>
              <a href="#file-types" className="list-group-item list-group-item-action">
                Supported File Types
              </a>
              <a href="#validation" className="list-group-item list-group-item-action">
                Data Validation
              </a>
              <a href="#troubleshooting" className="list-group-item list-group-item-action">
                Troubleshooting
              </a>
            </div>
          </div>
        </div>

        {/* Main Content */}
        <div className="col-md-9">
          <div className="card shadow-sm">
            <div className="card-body">
              <section id="overview" className="mb-5">
                <h2 className="border-bottom pb-2 mb-4">Overview</h2>
                <div className="alert alert-info">
                  <h4 className="alert-heading">Welcome to CleanAid</h4>
                  <p className="mb-0">DSWD Region X's official data cleansing tool for the Ayuda program. This tool ensures data integrity and consistency across all beneficiary records.</p>
                </div>
              </section>

              <section id="getting-started" className="mb-5">
                <h2 className="border-bottom pb-2 mb-4">Getting Started</h2>
                <div className="row g-4">
                  <div className="col-md-4">
                    <div className="card h-100">
                      <div className="card-body text-center">
                        <i className="bi bi-upload display-4 text-primary mb-3"></i>
                        <h5>1. Upload Data</h5>
                        <p className="text-muted">Upload your Excel or CSV file containing beneficiary information</p>
                      </div>
                    </div>
                  </div>
                  <div className="col-md-4">
                    <div className="card h-100">
                      <div className="card-body text-center">
                        <i className="bi bi-search display-4 text-primary mb-3"></i>
                        <h5>2. Analysis</h5>
                        <p className="text-muted">System automatically analyzes and validates your data</p>
                      </div>
                    </div>
                  </div>
                  <div className="col-md-4">
                    <div className="card h-100">
                      <div className="card-body text-center">
                        <i className="bi bi-file-earmark-text display-4 text-primary mb-3"></i>
                        <h5>3. Review Results</h5>
                        <p className="text-muted">View and export detailed validation results</p>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <section id="file-types" className="mb-5">
                <h2 className="border-bottom pb-2 mb-4">Supported File Types</h2>
                <div className="row">
                  <div className="col-md-6">
                    <div className="card mb-3">
                      <div className="card-body">
                        <h5 className="card-title">
                          <i className="bi bi-file-earmark-excel text-success me-2"></i>
                          Excel Files
                        </h5>
                        <p className="card-text">Supports both .xlsx and .xls formats</p>
                        <ul className="list-unstyled">
                          <li><i className="bi bi-check-circle-fill text-success me-2"></i>Multiple sheets</li>
                          <li><i className="bi bi-check-circle-fill text-success me-2"></i>Formatted cells</li>
                          <li><i className="bi bi-check-circle-fill text-success me-2"></i>Up to 1,048,576 rows</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  <div className="col-md-6">
                    <div className="card mb-3">
                      <div className="card-body">
                        <h5 className="card-title">
                          <i className="bi bi-file-earmark-text text-primary me-2"></i>
                          CSV Files
                        </h5>
                        <p className="card-text">Comma-separated values format</p>
                        <ul className="list-unstyled">
                          <li><i className="bi bi-check-circle-fill text-success me-2"></i>UTF-8 encoding</li>
                          <li><i className="bi bi-check-circle-fill text-success me-2"></i>Header row required</li>
                          <li><i className="bi bi-check-circle-fill text-success me-2"></i>No size limit</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <section id="validation" className="mb-5">
                <h2 className="border-bottom pb-2 mb-4">Data Validation Rules</h2>
                <div className="table-responsive">
                  <table className="table table-hover">
                    <thead className="table-light">
                      <tr>
                        <th>Field</th>
                        <th>Validation Rule</th>
                        <th>Example</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><strong>Name</strong></td>
                        <td>Required, no special characters</td>
                        <td>Juan Dela Cruz</td>
                      </tr>
                      <tr>
                        <td><strong>Age</strong></td>
                        <td>Required, must be a number</td>
                        <td>25</td>
                      </tr>
                      <tr>
                        <td><strong>Address</strong></td>
                        <td>Required, valid format</td>
                        <td>123 Main St, City</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </section>

              <section id="troubleshooting" className="mb-5">
                <h2 className="border-bottom pb-2 mb-4">Troubleshooting</h2>
                <div className="accordion" id="troubleshootingAccordion">
                  <div className="accordion-item">
                    <h2 className="accordion-header">
                      <button className="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                        File Upload Issues
                      </button>
                    </h2>
                    <div id="collapseOne" className="accordion-collapse collapse show" data-bs-parent="#troubleshootingAccordion">
                      <div className="accordion-body">
                        <ul className="list-unstyled">
                          <li><i className="bi bi-exclamation-circle text-warning me-2"></i>Ensure file is not password protected</li>
                          <li><i className="bi bi-exclamation-circle text-warning me-2"></i>Check file size (max 10MB)</li>
                          <li><i className="bi bi-exclamation-circle text-warning me-2"></i>Verify file format (.xlsx, .xls, .csv)</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Documentation; 