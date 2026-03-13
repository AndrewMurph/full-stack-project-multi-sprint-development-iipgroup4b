[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/CpAyDH2b)
# Sprint 0 – Web Application Project

## 🎯 Project Goal
In Sprint 0 you will design UI screens, plan architecture, and set up CI/CD.

---

## 🚀 CI/CD with GitHub Actions – What It Does

This project uses **GitHub Actions** to automatically run tasks when you push code to the repository. This is called **CI/CD** — Continuous Integration and Continuous Deployment.

You don’t need to install anything — it runs in the cloud for free.

---

### 🧠 What Happens Behind the Scenes?

When you push code to GitHub, the `.github/workflows/ci.yml` file tells GitHub what to do.

| Action | Trigger | What it does |
|--------|----------|--------------|
| Build Step | Push to `main` or `develop` | Simulates installing and testing |
| Deploy to DEV | Push to `develop` | Prints “Deploying to DEV...” |
| Deploy to PROD | Push to `main` | Prints “Deploying to PROD...” |

You can see results in the **Actions** tab.

---

### 📁 Where Is the Workflow File?

.github/workflows/ci.yml

That’s the “to-do list” GitHub follows every time you push code.

---

### 🛠️ Do You Need to Change It?

For Sprint 0, **no**.

You can explore and experiment with it, but it’s already set up to show how CI/CD works.

Later, you could:
- Replace `echo` with real tests  
- Simulate uploading files or deployments  

---

### ✅ Why This Matters

CI/CD is used in real-world software teams. Learning it early helps you:
- Avoid manual mistakes  
- Understand how modern dev teams deliver code  
- Build good habits for testing and deployment  


github intergration for jira
