# SPK Seleksi Anggota Paskibra — Fuzzy Sugeno

A web-based Decision Support System (DSS) designed to assist the selection of school-level Paskibra members in Tangerang Regency using the **Fuzzy Sugeno method**.

## About the Project

This project was developed as my undergraduate final project in Informatics Engineering at Universitas Pamulang.

The system helps selection committees evaluate candidates more consistently by processing multiple assessment criteria through fuzzy logic. Instead of relying solely on rigid numerical thresholds, the system calculates membership degrees, evaluates fuzzy rules, and generates a final recommendation score.

The goal is to support the selection process with a structured, transparent, and consistent evaluation method.

## Assessment Criteria

The system uses three main criteria:

| Criterion | Description |
|---|---|
| Height | Evaluates the candidate's height based on predefined fuzzy membership ranges. |
| Knowledge Test | Measures the candidate's knowledge score. |
| Interview | Evaluates the candidate's interview score. |

Each criterion is represented by three fuzzy sets: **Low, Medium, and High**.

## Methodology: Fuzzy Sugeno

The system applies the Fuzzy Sugeno inference method through the following stages:

1. **Fuzzification** — Converts input values into fuzzy membership degrees between 0 and 1.
2. **Rule Evaluation** — Evaluates combinations of fuzzy criteria using predefined IF–THEN rules.
3. **Firing Strength Calculation** — Calculates each rule's activation strength using the minimum operator (AND).
4. **Rule Output** — Assigns a constant output value \(z\) to each applicable Sugeno rule.
5. **Defuzzification** — Calculates the final score using a weighted average.

The final output is calculated using:

\[
Z=\frac{\sum_{i=1}^{n}\alpha_i z_i}
{\sum_{i=1}^{n}\alpha_i}
\]

Where:

- \(Z\) = Final recommendation score
- \(\alpha_i\) = Firing strength of rule \(i\)
- \(z_i\) = Constant output value of rule \(i\)
- \(n\) = Number of evaluated rules

The final score is used to support the candidate evaluation process according to the system's configured decision rules.

## Key Features

- Web-based decision support system.
- Candidate assessment using three selection criteria.
- Fuzzy membership function calculations.
- Fuzzy rule management.
- Fuzzy Sugeno inference and firing strength calculations.
- Weighted-average defuzzification.
- Final candidate recommendation scores.
- Role-based access for administrators and participants, according to the implemented modules.

## Technology Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript, Bootstrap
- **Dependency Management:** Composer

## Project Structure

```text
sistemPenunjangKeputusan/
├── assets/       # Frontend assets
├── config/       # Application configuration
├── db/           # Database-related files
├── layout/       # Shared interface layouts
├── pages/        # Application pages
├── service/      # Application services and processing logic
├── vendor/       # Composer dependencies
├── app.php       # Application entry point
├── home.php      # Home page
├── panduan.html  # User guide
├── composer.json
└── README.md
```

## Project Objectives

- To implement the Fuzzy Sugeno method in a web-based decision support system.
- To process candidate assessment data using fuzzy membership functions and inference rules.
- To produce consistent recommendation scores based on predefined criteria and rules.
- To support the selection committee in evaluating candidates systematically.

## Academic Information

**Project Type:** Undergraduate Final Project  
**Field:** Informatics Engineering  
**Institution:** Universitas Pamulang  
**Research Topic:** Web-Based Decision Support System for School-Level Paskibra Member Selection in Tangerang Regency Using the Fuzzy Sugeno Method.

## Future Improvements

- Improve the presentation of fuzzy calculation results.
- Add more detailed evaluation reports.
- Enhance usability and interface responsiveness.
- Expand system testing and validation using real assessment data.

## Author

**Alfino Nicolas Prasetya**  
Informatics Engineering Graduate — Universitas Pamulang

GitHub: [@alfinoNicolasPrasetya](https://github.com/alfinoNicolasPrasetya)

---

*This project was developed for academic purposes as an implementation of a fuzzy logic-based decision support system.*
