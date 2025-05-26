import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';

interface ResultItem {
  id: number;
  issue_type: string;
  description: string;
  row_number: number;
  value: string;
}

const Results: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const [results, setResults] = useState<ResultItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [uploadDetails, setUploadDetails] = useState<{ filename: string; upload_date: string; } | null>(null);

  useEffect(() => {
    const fetchResults = async () => {
      if (!id) {
        setError('No upload ID provided.');
        setLoading(false);
        return;
      }

      // TODO: Adjust the URL based on your PHP backend setup
      const resultsUrl = `/CleanAid_Caps/results.php?upload_id=${id}`; // Assuming results.php handles fetching results

      try {
        const response = await fetch(resultsUrl);
        const data = await response.json();

        if (response.ok && data.success) {
          setUploadDetails(data.uploadDetails);
          setResults(data.results);
        } else {
          setError(data.message || 'Failed to fetch results.');
        }
      } catch (err) {
        console.error('Fetch results error:', err);
        setError('An error occurred while fetching results.');
      } finally {
        setLoading(false);
      }
    };

    fetchResults();
  }, [id]); // Re-run effect if upload ID changes

  // Helper function to get Bootstrap badge class based on issue type
  const getBadgeClass = (type: string) => {
    switch (type.toLowerCase()) {
      case 'duplicate':
        return 'bg-warning';
      case 'inconsistent':
        return 'bg-danger';
      case 'missing':
        return 'bg-secondary';
      default:
        return 'bg-primary';
    }
  };

  const handleExport = () => {
    // TODO: Implement export functionality
    // You would typically redirect to your export.php with the upload ID
    window.location.href = `/CleanAid_Caps/export.php?upload_id=${id}`;
  };

  return (
    <div className="container mt-4">
      <div className="row justify-content-center">
        <div className="col-md-10">
          <div className="card shadow-sm">
            <div className="card-body">
              <h2 className="card-title mb-4 text-center">Data Cleansing Results</h2>

              {loading && <div className="alert alert-info">Loading results...</div>}
              {error && <div className="alert alert-danger">{error}</div>}

              {!loading && !error && uploadDetails && (
                <div className="mb-4">
                  <h4>Upload Details</h4>
                  <p><strong>File Name:</strong> {uploadDetails.filename}</p>
                  <p><strong>Upload Date:</strong> {uploadDetails.upload_date}</p>
                </div>
              )}

              {!loading && !error && results.length > 0 && (
                <div className="table-responsive mb-4">
                  <h4>Issues Found</h4>
                  <table className="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>Issue Type</th>
                        <th>Description</th>
                        <th>Row Number</th>
                        <th>Value</th>
                      </tr>
                    </thead>
                    <tbody>
                      {results.map(result => (
                        <tr key={result.id}>
                          <td><span className={`badge ${getBadgeClass(result.issue_type)}`}>{result.issue_type}</span></td>
                          <td>{result.description}</td>
                          <td>{result.row_number}</td>
                          <td>{result.value}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}

               {!loading && !error && results.length === 0 && !uploadDetails && (
                <div className="alert alert-warning">No results found for this upload ID.</div>
              )}

               {!loading && !error && results.length === 0 && uploadDetails && (
                <div className="alert alert-success">No issues found in {uploadDetails.filename}!</div>
              )}

              {!loading && !error && (results.length > 0 || uploadDetails) && (
                 <div className="text-center">
                    <button className="btn btn-primary" onClick={handleExport}>
                      Export Results (CSV)
                    </button>
                 </div>
              )}

            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Results; 